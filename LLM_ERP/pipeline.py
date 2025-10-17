# ======== PIPELINE OPTIMISÉ — DROP-IN REPLACEMENT ========
import time
import datetime
import re
import torch
from concurrent.futures import ThreadPoolExecutor
from typing import Tuple, List, Dict
from functools import lru_cache
import logging
import cx_Oracle
from transformers import LogitsProcessor, LogitsProcessorList


from oracle_pool import OraclePool
from prompts import build_llama_combined_reason_and_answer_prompt2

from config import (
    SCHEMA_SQL_detaille_final
)

from sanitize import (
    sanitize_sql_for_oracle,
    patch_tauxengagement_sql,
    normalize_temporal_literals,
    shorten_sql_aliases
)

from canonicalize import (
    canonicalize_question, add_like_lower, patch_numjc_date_extraction,
    normalize_aliases_to_vec_family, filter_columns_from_schema_dynamic,
    build_python_natural_multirow_answer
)

# Modèles & tokens pré-chargés (tu les as dans llm_sessions)
from llm_session2 import (
    tokenizer_sqlcoder_8b, model_sqlcoder_8b,
    tokenizer_Llama, modelsLlama,
    PRETOKENIZED_PROMPT_PREFIX_8B, PRETOKENIZED_SCHEMA_8B, PRETOKENIZED_PROMPT_SUFFIX_8B, PRETOKENIZED_PROMPT_SUFFIX2_8B,
    PRETOKENIZED_PROMPT_PREFIX_LLAMA, PRETOKENIZED_PROMPT_SUFFIX1_LLAMA, PRETOKENIZED_SCHEMA_LLAMA,
    PRETOKENIZED_PROMPT_SUFFIX2_LLAMA, PRETOKENIZED_PROMPT_SUFFIX3_LLAMA, PRETOKENIZED_PROMPT_SUFFIX4_LLAMA,
    PRETOKENIZED_STATIC_PROMPT_8B
)

from transformers import logging as transformers_logging
transformers_logging.set_verbosity_error()

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s',
    handlers=[logging.FileHandler("pipeline.log", encoding="utf-8"), logging.StreamHandler()]
)

# --------------------------
# Réglages perf & sécurité
# --------------------------
MAX_Q_LEN = 96          
MAX_SQL_LEN = 200       
GEN_SQL_TOKENS = 128      
GEN_LLAMA_TOKENS = 256  

USE_FILTERED_SCHEMA_FOR_LLAMA = False  # True = passer schéma RAG réduit au validateur LLaMA

# Attention accélérée si dispo (ne change rien au workflow)
for _m in (model_sqlcoder_8b, modelsLlama):
    try:
        # "flash_attention_2" si installé, sinon SDPA
        _m.config.attn_implementation = getattr(_m.config, "attn_implementation", "sdpa")
    except Exception:
        pass

# ---- Stopping criteria
from transformers import StoppingCriteria, StoppingCriteriaList


class StopOnRegexAllBatch(StoppingCriteria):
    """
    Stoppe quand TOUTES les séquences du batch matchent le pattern.
    (ton StopOnRegex ne regardait que l'item 0 -> pas adapté au batch=2)
    """
    def __init__(self, tokenizer, pattern, tail_tokens: int = 120):
        import re
        self.tok = tokenizer
        self.re = re.compile(pattern, re.IGNORECASE | re.DOTALL)
        self.tail = tail_tokens

    def __call__(self, input_ids, scores):
        # input_ids: [batch, seq]
        for i in range(input_ids.size(0)):
            tail_ids = input_ids[i, -self.tail:].tolist()
            text = self.tok.decode(tail_ids, skip_special_tokens=True)
            if not self.re.search(text):
                return False
        return True


class PerExampleGreedyHotProcessor(LogitsProcessor):
    """
    Pour un seul generate() batché:
      - indices 'greedy' : force l'argmax à chaque pas (équiv. do_sample=False)
      - indices 'hot'    : applique temperature + top-k + top-p
    """
    def __init__(self, greedy_indices=(0,), hot_indices=(1,), temperature=0.9, top_p=0.95, top_k=50):
        self.greedy = set(greedy_indices)
        self.hot = set(hot_indices)
        self.temperature = max(temperature, 1e-6)
        self.top_p = top_p
        self.top_k = top_k

    def __call__(self, input_ids, scores):
        # scores: [batch, vocab]
        # 1) Greedy: force argmax
        for i in self.greedy:
            if i < scores.size(0):
                row = scores[i]
                argmax = torch.argmax(row)
                row.fill_(-float('inf'))
                row[argmax] = 0.0  # prob=1 après softmax

        # 2) Hot: temperature + top-k + nucleus (top-p)
        for i in self.hot:
            if i < scores.size(0):
                row = scores[i] / self.temperature

                # top-k
                if self.top_k and 0 < self.top_k < row.numel():
                    kth_vals, _ = torch.topk(row, self.top_k)
                    min_keep = kth_vals[..., -1]
                    row[row < min_keep] = -float('inf')

                # top-p (nucleus)
                if self.top_p and 0.0 < self.top_p < 1.0:
                    sorted_logits, sorted_idx = torch.sort(row, descending=True)
                    probs = torch.softmax(sorted_logits, dim=-1)
                    cumprobs = torch.cumsum(probs, dim=-1)
                    mask = cumprobs > self.top_p
                    # garder au moins 1 token
                    mask[..., 1:] = mask[..., :-1].clone()
                    mask[..., 0] = False
                    row[sorted_idx[mask]] = -float('inf')

                scores[i] = row

        return scores


class StopOnRegex(StoppingCriteria):
    def __init__(self, tokenizer, pattern):
        import re
        self.tok = tokenizer
        self.re = re.compile(pattern, re.IGNORECASE | re.DOTALL)
    def __call__(self, input_ids, scores):
        tail = input_ids[0, -120:].tolist()
        text = self.tok.decode(tail, skip_special_tokens=True)
        return bool(self.re.search(text))


STOP_SQLCODER_8B = StoppingCriteriaList([StopOnRegex(tokenizer_sqlcoder_8b, r"(SELECT|WITH).+?;")])
STOP_LLAMA       = StoppingCriteriaList([StopOnRegex(tokenizer_Llama, r"\[/SQL\]")])
STOP_FMT = StoppingCriteriaList([StopOnRegex(tokenizer_Llama, r"\[END\]")])

# ---- Utils extraction blocs
def _extract_block(text: str, start_label: str, end_labels: List[str]) -> str:
    pattern = re.compile(
        rf"{re.escape(start_label)}\s*:\s*(.*?)"
        rf"(?=(?:{'|'.join(map(re.escape, end_labels))})\s*:|$)",
        flags=re.IGNORECASE | re.DOTALL,
    )
    matches = pattern.findall(text)
    if not matches:
        return ""
    block = matches[-1]
    block = re.split(
        r"\b(?:Format attendu|Question utilisateur|Requête SQL générée)\b.*",
        block, flags=re.IGNORECASE,
    )[0]
    block = re.sub(r"\s+", " ", block).strip()
    return block

def extract_llama_raison(text: str) -> str:
    raison = _extract_block(text, "Raison", ["Réponse naturelle"])
    idx = raison.lower().find("vous souhaitez obtenir")
    return raison[idx:] if idx != -1 else raison

def extract_llama_reponse(text: str) -> str:
    return _extract_block(text, "Réponse naturelle", ["Raison"])

def extract_sql_only(generated_text: str) -> str:
    if "SQL Query:" in generated_text:
        sql_part = generated_text.split("SQL Query:")[-1]
    else:
        sql_part = generated_text
    sql_part = sql_part.lstrip()
    m = re.search(r"^(SELECT|WITH)\b.*", sql_part, re.IGNORECASE | re.DOTALL | re.MULTILINE)
    if m:
        sql = m.group(0).strip()
        if not sql.endswith(";"):
            sql += ";"
        return sql
    return sql_part.strip()

def extract_sql_only_LLama(text: str) -> str:
    if not text:
        return ""

    # Cas A : le texte contient [SQL] ... [/SQL]
    m = re.search(r"\[SQL\](.+?)\[/SQL\]", text, flags=re.IGNORECASE | re.DOTALL)
    if m:
        body = m.group(1)
    else:
        # Cas B : on n'a que [/SQL] (ou aucun tag) car [SQL] était dans le prompt
        if "[/SQL]" in text:
            body = text.split("[/SQL]")[0]
        else:
            body = text

    # Récupère la requête (dernier SELECT/WITH) dans body
    m2 = re.search(r"(?is)\b(SELECT|WITH)\b.*?(?=;|$)", body)
    if not m2:
        return ""

    sql = m2.group(0).strip()
    if not sql.endswith(";"):
        sql += ";"

    # Nettoyage léger
    sql = re.sub(r";{2,}", ";", sql)
    sql = re.sub(r"\s+", " ", sql).strip()
    return sql


# ---- Tokenisation fixe pour LLaMA validateur
def _tok_llama(txt: str, maxlen: int):
    return tokenizer_Llama(
        txt, return_tensors="pt", add_special_tokens=False,
        truncation=True, max_length=maxlen, padding="max_length"
    )["input_ids"].to("cuda", non_blocking=True)

# =========================
#   Génération SQLCoder
# =========================

def generate_sqlcoder_sql2(question: str, schema: str = None) -> str:
    q = tokenizer_sqlcoder_8b(
        question,
        return_tensors="pt",
        add_special_tokens=False,
        truncation=True,
        max_length=MAX_Q_LEN,
        padding="max_length"
    )
    q_ids  = q["input_ids"].pin_memory().to("cuda", non_blocking=True)
    q_mask = q["attention_mask"].pin_memory().to("cuda", non_blocking=True)

    input_ids = torch.cat([PRETOKENIZED_STATIC_PROMPT_8B, q_ids, PRETOKENIZED_PROMPT_SUFFIX2_8B], dim=1)
    attention_mask = torch.cat([
        torch.ones_like(PRETOKENIZED_STATIC_PROMPT_8B),
        q_mask,
        torch.ones_like(PRETOKENIZED_PROMPT_SUFFIX2_8B)
    ], dim=1)

    prompt_len = input_ids.shape[1]
    context_max = getattr(model_sqlcoder_8b.config, "max_position_embeddings", 4096)

    model_sqlcoder_8b.eval()
    with torch.inference_mode():
        outputs = model_sqlcoder_8b.generate(
            input_ids=input_ids,
            attention_mask=attention_mask,
            max_new_tokens=min(GEN_SQL_TOKENS, context_max - prompt_len),
            do_sample=False,
            num_beams=1,
            use_cache=True,
            stopping_criteria=STOP_SQLCODER_8B,
            eos_token_id=tokenizer_sqlcoder_8b.eos_token_id,
            pad_token_id=tokenizer_sqlcoder_8b.pad_token_id,
        )

    # ⬇️ ne décode que la continuation
    gen_only = outputs[0, prompt_len:]
    sql_generated = tokenizer_sqlcoder_8b.decode(gen_only, skip_special_tokens=True)
    return extract_sql_only(sql_generated)


def generate_sqlcoder_two_modes_single_call(
    question: str,
    hot_temperature: float = 0.1,
    hot_top_p: float = 0.1,
    hot_top_k: int = 1,
) -> Tuple[str, str]:
    """
    Retourne (sql_det, sql_hot) en UNE seule passe GPU:
      - batch[0] = greedy (sans température)
      - batch[1] = hot (température)
    """
    # Prépare les entrées (une seule fois)
    q = tokenizer_sqlcoder_8b(
        question,
        return_tensors="pt",
        add_special_tokens=False,
        truncation=True,
        max_length=MAX_Q_LEN,
        padding="max_length"
    )
    q_ids  = q["input_ids"].pin_memory().to("cuda", non_blocking=True)
    q_mask = q["attention_mask"].pin_memory().to("cuda", non_blocking=True)

    base_input_ids = torch.cat([PRETOKENIZED_STATIC_PROMPT_8B, q_ids, PRETOKENIZED_PROMPT_SUFFIX2_8B], dim=1)
    base_attention = torch.cat([
        torch.ones_like(PRETOKENIZED_STATIC_PROMPT_8B),
        q_mask,
        torch.ones_like(PRETOKENIZED_PROMPT_SUFFIX2_8B)
    ], dim=1)

    # On duplique pour batch=2 (même prompt)
    input_ids = base_input_ids.repeat(2, 1)
    attention_mask = base_attention.repeat(2, 1)

    prompt_len = base_input_ids.shape[1]
    context_max = getattr(model_sqlcoder_8b.config, "max_position_embeddings", 4096)
    max_new = max(1, min(GEN_SQL_TOKENS, context_max - prompt_len))

    # Processors: 0=greedy, 1=hot
    lp = LogitsProcessorList([
        PerExampleGreedyHotProcessor(
            greedy_indices=(0,),
            hot_indices=(1,),
            temperature=hot_temperature,
            top_p=hot_top_p,
            top_k=hot_top_k,
        )
    ])

    # Stopper: s'arrête quand les DEUX séquences ont atteint ; (ou pattern)
    stop_both = StoppingCriteriaList([
        StopOnRegexAllBatch(tokenizer_sqlcoder_8b, r"(SELECT|WITH).+?;")
    ])

    model_sqlcoder_8b.eval()
    with torch.inference_mode():
        outputs = model_sqlcoder_8b.generate(
            input_ids=input_ids,
            attention_mask=attention_mask,
            max_new_tokens=max_new,
            do_sample=True,                 # sampling global (mais contrôlé par notre processor)
            num_return_sequences=1,         # 1 par item du batch
            use_cache=True,
            logits_processor=lp,            # clé: per-example decoding
            stopping_criteria=stop_both,    # stop quand les 2 ont fini
            eos_token_id=tokenizer_sqlcoder_8b.eos_token_id,
            pad_token_id=tokenizer_sqlcoder_8b.pad_token_id,
        )

    # Décodage: chaque ligne du batch retourne une séquence
    det_text = tokenizer_sqlcoder_8b.decode(outputs[0, prompt_len:], skip_special_tokens=True)
    hot_text = tokenizer_sqlcoder_8b.decode(outputs[1, prompt_len:], skip_special_tokens=True)

    sql_det = extract_sql_only(det_text)
    sql_hot = extract_sql_only(hot_text)

    # Si (rare) identiques, on peut augmenter un peu la chaleur et relancer juste la ligne hot
    if re.sub(r"\s+", " ", sql_det.strip()).lower() == re.sub(r"\s+", " ", sql_hot.strip()).lower():
        with torch.inference_mode():
            # on relance uniquement batch=1 (simple astuce: refaire un appel 1x avec temp plus haute)
            outputs2 = model_sqlcoder_8b.generate(
                input_ids=base_input_ids,             # batch=1
                attention_mask=base_attention,
                max_new_tokens=max_new,
                do_sample=True,
                temperature=max(1.1, hot_temperature + 0.2),
                top_p=min(0.98, hot_top_p + 0.02),
                top_k=hot_top_k,
                num_return_sequences=1,
                use_cache=True,
                stopping_criteria=STOP_SQLCODER_8B,   # OK mono-batch
                eos_token_id=tokenizer_sqlcoder_8b.eos_token_id,
                pad_token_id=tokenizer_sqlcoder_8b.pad_token_id,
            )
        sql_hot = extract_sql_only(tokenizer_sqlcoder_8b.decode(outputs2[0, prompt_len:], skip_special_tokens=True))

    return sql_det, sql_hot



# =========================
#   Validation LLaMA
# =========================
def _tok_llama_pair(txt: str, maxlen: int = None):
    out = tokenizer_Llama(
        txt,
        return_tensors="pt",
        add_special_tokens=False,
        padding=False,      # pas de padding à longueur fixe
        truncation=False    # pas de troncature
    )
    return (out["input_ids"].to("cuda", non_blocking=True),
            out["attention_mask"].to("cuda", non_blocking=True))

def generate_validator_sql2(question: str, sql1: str, sql2: str) -> str:
    q_ids,   q_mask   = _tok_llama_pair(question, MAX_Q_LEN)
    s1_ids,  s1_mask  = _tok_llama_pair(sql1,     MAX_SQL_LEN)
    s2_ids,  s2_mask  = _tok_llama_pair(sql2,     MAX_SQL_LEN)

    if USE_FILTERED_SCHEMA_FOR_LLAMA:
        rag_schema = filter_columns_from_schema_dynamic(
            question, SCHEMA_SQL_detaille_final, top_k_tables=4, top_k_cols=60
        )
        sch_ids, sch_mask = _tok_llama_pair(rag_schema, MAX_SQL_LEN)
    else:
        sch_ids  = PRETOKENIZED_SCHEMA_LLAMA
        sch_mask = torch.ones_like(PRETOKENIZED_SCHEMA_LLAMA)

    input_ids = torch.cat([
        PRETOKENIZED_PROMPT_PREFIX_LLAMA,
        q_ids,
        PRETOKENIZED_PROMPT_SUFFIX1_LLAMA,
        sch_ids,
        PRETOKENIZED_PROMPT_SUFFIX2_LLAMA,
        s1_ids,
        PRETOKENIZED_PROMPT_SUFFIX3_LLAMA,
        s2_ids,
        PRETOKENIZED_PROMPT_SUFFIX4_LLAMA,
    ], dim=1)

    attention_mask = torch.cat([
        torch.ones_like(PRETOKENIZED_PROMPT_PREFIX_LLAMA),
        q_mask,
        torch.ones_like(PRETOKENIZED_PROMPT_SUFFIX1_LLAMA),
        sch_mask,
        torch.ones_like(PRETOKENIZED_PROMPT_SUFFIX2_LLAMA),
        s1_mask,
        torch.ones_like(PRETOKENIZED_PROMPT_SUFFIX3_LLAMA),
        s2_mask,
        torch.ones_like(PRETOKENIZED_PROMPT_SUFFIX4_LLAMA),
    ], dim=1)

    prompt_len = input_ids.shape[1]
    context_max = getattr(modelsLlama.config, "max_position_embeddings", 8192)

    modelsLlama.eval()
    with torch.inference_mode():
        outputs = modelsLlama.generate(
            input_ids=input_ids,
            attention_mask=attention_mask,
            max_new_tokens=min(GEN_LLAMA_TOKENS, context_max - prompt_len),
            do_sample=False,
            num_beams=1,
            use_cache=True,
            stopping_criteria=STOP_LLAMA,
            eos_token_id=tokenizer_Llama.eos_token_id,
            pad_token_id=tokenizer_Llama.pad_token_id,
        )

    # ⬇️ ne décode que la continuation
    gen_only = outputs[0, prompt_len:]
    return tokenizer_Llama.decode(gen_only, skip_special_tokens=True)


# =========================
#   Oracle execution
# =========================
def try_execute_sql(sql: str, source: str = "") -> dict:
    conn = None
    try:
        sql = sql.rstrip().rstrip(";")
        conn = OraclePool.acquire_connection()
        cursor = conn.cursor()

        # perf I/O
        cursor.arraysize = 100
        try:
            conn.prefetchrows = 100
        except Exception:
            pass

        logging.info(f"[{source}] Trying SQL:\n{sql}")
        cursor.execute(sql)
        columns = [col[0] for col in cursor.description]
        rows = cursor.fetchall()
        cursor.close()
        logging.info(f"[{source}] Success ✅ — {len(rows)} rows fetched")
        return {"success": True, "columns": columns, "rows": rows}
    except Exception as e:
        logging.error(f"[{source}] SQL FAILED ❌: {str(e)}")
        return {"success": False, "error": str(e)}
    finally:
        if conn:
            conn.close()

def is_aggregate_query(sql: str) -> bool:
    return bool(re.search(r"\b(SUM|AVG|COUNT|MIN|MAX)\s*\(", sql, re.IGNORECASE))

@lru_cache(maxsize=200)
def try_execute_sql_cached(sql: str, source="") -> dict:
    return try_execute_sql(sql, source)

def limit_sql_rows(sql: str, max_rows: int = 100) -> str:
    sql = sql.strip().rstrip(';')
    if re.search(r"\bROWNUM\b", sql, flags=re.IGNORECASE):
        return sql + ";"
    return f"SELECT * FROM ({sql}) WHERE ROWNUM <= {max_rows};"

# =========================
#   Pipeline complet
# =========================
def process_question_pipeline(question: str, max_post_exec_loops: int = 3) -> dict:
    t0_total = time.perf_counter()
    log_steps = {"timings": {}}

    def log_time(step_name: str, t_start: float, extra: dict = None):
        duration = round(time.perf_counter() - t_start, 3)
        details = extra or {}
        log_steps["timings"][step_name] = {
            "duration": duration,
            "details": details
        }
        # --- Écriture dans un fichier .log ---
        try:
            with open("pipeline_steps.log", "a", encoding="utf-8") as f:
                ts = datetime.datetime.now().isoformat(timespec="seconds")
                f.write(f"[{ts}] STEP: {step_name} | Duration: {duration}s | Details: {details}\n")
        except Exception as e:
            logging.error(f"Erreur écriture log étape {step_name}: {e}")


    # 1) Canonicalisation
    t1 = time.perf_counter()
    question_explicit = canonicalize_question(question)
    sub_schema_sql = SCHEMA_SQL_detaille_final  # on ne change pas le contenu ici
    log_time("01_rag", t1, {"question_explicit": question_explicit, "schema_snippet": sub_schema_sql[:400]})

    # 2) Générations 7B & 8B
    ngpus = torch.cuda.device_count()
    t8b_2 = time.perf_counter()
    t8b = time.perf_counter()

    # Prop1 = greedy ; Prop2 = hot (UNE SEULE PASSE)
    sql_det, sql_hot = generate_sqlcoder_two_modes_single_call(
        question_explicit,
        hot_temperature=0.1
    )

    # IMPORTANT pour ton validateur (il prend sql1=prop1, sql2=prop2)
    sql_8b_raw2 = sql_det    # Proposition SQL 1 (déterministe)
    sql_8b_raw  = sql_hot    # Proposition SQL 2 (chaude)

    print("sql_coder1 avant les fct: ", sql_8b_raw2)
    print("sql_coder2 avant les fct: ", sql_8b_raw)

    log_time("02a_generation_7b", t8b_2, {"sql_7b_raw": sql_8b_raw2})
    log_time("02b_generation_8b", t8b, {"sql_8b_raw": sql_8b_raw})

    # 3) Validation LLaMA en parallèle du nettoyage
    t_llama_val = time.perf_counter()
    with ThreadPoolExecutor(max_workers=1) as executor:
        future_validation = executor.submit(generate_validator_sql2, question_explicit, sql_8b_raw2, sql_8b_raw)


        # Nettoyage deux SQL candidates
        sql_code1 = normalize_aliases_to_vec_family(add_like_lower(sanitize_sql_for_oracle(sql_8b_raw2)))
        sql_code2 = normalize_aliases_to_vec_family(add_like_lower(sanitize_sql_for_oracle(sql_8b_raw)))
        log_steps["sql_generated_8b_2"] = sql_code1
        log_steps["sql_generated_8b"] = sql_code2

        generated_text = future_validation.result()
    log_time("03_llama_validation", t_llama_val, {"llama_validation_text": generated_text})

    # 4) Extraction & post-process
    t_postproc = time.perf_counter()
    print(generated_text)
    final_sql = extract_sql_only_LLama(generated_text)
    print("final sql avant les fct: ", final_sql)
    final_sql = shorten_sql_aliases(normalize_temporal_literals(
        normalize_aliases_to_vec_family(add_like_lower(sanitize_sql_for_oracle(final_sql))),
        eod=True
    ))
    if (
        not final_sql or
        "FROM..." in final_sql or
        "..." in final_sql or
        len(final_sql) < 15 or
        re.search(r'SELECT\s+SUM\([^)]+\)\s+FROM\s*\.\.\.', final_sql, re.I)
    ):
        print("[WARN] Fallback sur la proposition du modèle 2 (8b)")
        final_sql = sql_code1

    log_steps["validated_sql_block"] = generated_text
    log_steps["final_sql_qwen"] = final_sql
    log_time("04_post_validation_processing", t_postproc, {"final_sql": final_sql})

    # 5) Préparation SQL pour exécution Oracle
    t5 = time.perf_counter()
    llama_validated_sql = final_sql
    sql_to_run = patch_tauxengagement_sql(sanitize_sql_for_oracle(llama_validated_sql))
    log_steps["llama_final_sql"] = llama_validated_sql
    log_time("05_pre_oracle_sql", t5, {"sql_to_run": sql_to_run})

    # 6) Exécution Oracle (limit rows si non-agrégat)
    # 6) Exécution Oracle (limit rows si non-agrégat)
    t6 = time.perf_counter()
    if is_aggregate_query(sql_to_run):
        sql_for_exec = sql_to_run
    else:
        sql_for_exec = limit_sql_rows(sql_to_run, 20)

    execution_result = try_execute_sql_cached(sql_for_exec)
    log_steps["oracle_executions"] = [{"attempt": 1, "sql": sql_to_run, "result": execution_result}]
    log_time("06_oracle_execution", t6, {"oracle_sql": sql_to_run, "oracle_result": execution_result})

    # === Nouveau bloc : Fallback si Oracle renvoie une erreur ===
    if not execution_result.get("success"):
        print("[WARN] Oracle error, fallback sur la proposition du modèle 2 (8b)")
        sql_to_run = sql_code1
        if is_aggregate_query(sql_to_run):
            sql_for_exec = sql_to_run
        else:
            sql_for_exec = limit_sql_rows(sql_code1, 20)
        execution_result = try_execute_sql_cached(sql_for_exec)
        log_steps["oracle_executions"].append({"attempt": 2, "sql": sql_to_run, "result": execution_result})


    # 7) Réponse naturelle LLaMA (budget dynamique)
    # 7) Réponse naturelle LLaMA (Raison + Réponse naturelle)
    t7 = time.perf_counter()
    output_str = ""  # évite NameError si erreur Oracle
    final_result = log_steps["oracle_executions"][-1]["result"]

    def _extract_reason_and_answer(gen_text: str):
        import re
        # On coupe au marqueur [END] s'il est présent
        gen_text = gen_text.split("[END]")[0].strip()
        # Récupération robuste des 2 lignes
        m_r = re.search(r"Raison\s*:\s*(.+)", gen_text, flags=re.IGNORECASE)
        m_a = re.search(r"Réponse\s*naturelle\s*:\s*(.+)", gen_text, flags=re.IGNORECASE)
        raison = (m_r.group(1).strip() if m_r else "")
        answer = (m_a.group(1).strip() if m_a else "")
        # Filet de sécu : si l’une manque, on prend dernière/avant-dernière ligne non vide
        if not (raison and answer):
            lines = [l.strip() for l in gen_text.splitlines() if l.strip()]
            if not raison and lines:
                raison = lines[0]
            if not answer and len(lines) >= 2:
                answer = lines[-1]
        return raison, answer

    if final_result.get("success"):
        columns = final_result.get("columns", [])
        rows = final_result.get("rows", [])
        is_single_value = (len(rows) == 1 and len(columns) == 1)
        is_empty = (not rows or rows is None)

        prompt = build_llama_combined_reason_and_answer_prompt2(
            question_explicit,
            sql_to_run,
            columns,
            rows[:20] if rows and len(rows) > 20 else rows
        )
        enc = tokenizer_Llama(prompt, return_tensors="pt").to("cuda")
        prompt_len = enc["input_ids"].shape[1]

        # Budget confortable (2 lignes) ; tu peux le laisser fixe
        budget = 160

        modelsLlama.eval()
        with torch.inference_mode():
            outputs = modelsLlama.generate(
                **enc,
                max_new_tokens=budget,
                do_sample=False,
                num_beams=1,
                use_cache=True,
                stopping_criteria=STOP_FMT,
                eos_token_id=tokenizer_Llama.eos_token_id,
                pad_token_id=tokenizer_Llama.pad_token_id,
            )

        # On ne décode QUE la continuation (après 'Raison: ')
        gen_only = outputs[0, prompt_len:]
        output_str = tokenizer_Llama.decode(gen_only, skip_special_tokens=True).strip()
        print("Réponse complet:", output_str)

        # Extraction des 2 lignes
        raison, user_friendly_answer = _extract_reason_and_answer(output_str)

        # Option : pour les résultats tabulaires (non single value), on ajoute le rappel d’affichage
        if (not is_empty) and (not is_single_value):
            user_friendly_answer = (
                user_friendly_answer.strip()
                + "  (Voir tableau ci-dessous)\n\n**NB : Seules les 20 premières lignes sont affichées pour des raisons de lisibilité.**"
            )

    else:
        raison = ""
        user_friendly_answer = "Une erreur Oracle est survenue, impossible de répondre pour l’instant."

    log_time("07_natural_answer_generation", t7, {"llama_output": output_str if final_result.get("success") else ""})


    # Finalisation
    t1_total = time.perf_counter()

    def clean_for_json(obj):
        if isinstance(obj, dict):
            return {k: clean_for_json(v) for k, v in obj.items() if k != 'log'}
        elif isinstance(obj, list):
            return [clean_for_json(i) for i in obj]
        elif isinstance(obj, (str, int, float, bool, type(None))):
            return obj
        else:
            return str(obj)

    def serialize_rows(rows):
        result = []
        for row in rows:
            row_serialized = []
            for item in row:
                if isinstance(item, (datetime.datetime, datetime.date)):
                    row_serialized.append(item.isoformat())
                else:
                    row_serialized.append(item)
            result.append(row_serialized)
        return result

    log_steps_clean = clean_for_json(log_steps)
    if final_result.get("rows"):
        final_result["rows"] = serialize_rows(final_result["rows"])
    for exec_ in log_steps["oracle_executions"]:
        if exec_["result"].get("rows"):
            exec_["result"]["rows"] = serialize_rows(exec_["result"]["rows"])

    return {
        "mode": "sql",
        "question_explicit": question_explicit,
        "schema_rag": sub_schema_sql,
        "sql_code_initial_a": sql_code1 if 'sql_code1' in locals() else "",
        "sql_code_initial_b": sql_code2 if 'sql_code2' in locals() else "",
        "sql_validated_llama": llama_validated_sql,
        "sql_validated_llama_0": final_sql,
        "sql_validated_post_exec": sql_to_run,
        "oracle_executions": log_steps["oracle_executions"],
        "final_result": final_result,
        "llama_reason": raison,
        "user_friendly_answer": user_friendly_answer,
        "duration_total_pipeline": round(t1_total - t0_total, 2),
        "log": log_steps_clean
    }

