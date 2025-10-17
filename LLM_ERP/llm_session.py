import os
os.environ["TRANSFORMERS_OFFLINE"] = "1"
os.environ["HF_HUB_OFFLINE"] = "1"

import torch
from transformers import AutoTokenizer, logging
from awq import AutoAWQForCausalLM
from config import SCHEMA_SQL_detaille_final

logging.set_verbosity_error()

# --- CUDA perf flags (sans FA2) ---
torch.backends.cuda.matmul.allow_tf32 = True
torch.backends.cudnn.allow_tf32 = True
try:
    from torch.backends.cuda import sdp_kernel
    sdp_kernel.enable_flash_sdp(False)       # pas de FA2 -> évite les warnings Windows
    sdp_kernel.enable_mem_efficient_sdp(True)
    sdp_kernel.enable_math_sdp(True)
except Exception:
    pass

# --- helpers ---
def ensure_pad_token(tokenizer, model):
    if tokenizer.pad_token is None:
        tokenizer.pad_token = tokenizer.eos_token
    model.config.pad_token_id = tokenizer.pad_token_id
    try:
        model.generation_config.pad_token_id = tokenizer.pad_token_id
    except Exception:
        pass
    try:
        tokenizer.padding_side = "right"
    except Exception:
        pass

def warmup_model(model, tokenizer, prompt="SELECT 1;"):
    if not torch.cuda.is_available():
        return
    input_ids = tokenizer(prompt, return_tensors="pt").to("cuda")
    with torch.inference_mode():
        _ = model.generate(**input_ids, max_new_tokens=8)
    print("🔥 Warmup modèle fait.")

# ============================================================================
#                     1) SQLCoder-8B (AWQ)
# ============================================================================
SQLCODER_AWQ_PATH = r"models\sqlcoder-8b-awq"

tokenizer_sqlcoder_8b = AutoTokenizer.from_pretrained(
    SQLCODER_AWQ_PATH, use_fast=True, local_files_only=True
)
model_sqlcoder_8b = AutoAWQForCausalLM.from_quantized(
    SQLCODER_AWQ_PATH,
    device_map={"": 0},         # ou "auto"
    fuse_layers=True,
    safetensors=True,
)
ensure_pad_token(tokenizer_sqlcoder_8b, model_sqlcoder_8b)
try:
    model_sqlcoder_8b.config.attn_implementation = "sdpa"
except Exception:
    pass
model_sqlcoder_8b.config.use_cache = True
print(f"✅ SQLCoder-8B (AWQ) chargé. VRAM utilisée : {torch.cuda.memory_allocated() / 1024**3:.2f} GB")
warmup_model(model_sqlcoder_8b, tokenizer_sqlcoder_8b)

# ============================================================================
#                     2) LLaMA3-8B (AWQ)
# ============================================================================
LLAMA_AWQ_PATH = r"models\LLaMA3-8B-awq"

tokenizer_Llama = AutoTokenizer.from_pretrained(
    LLAMA_AWQ_PATH, use_fast=True, local_files_only=True
)
LLAMA_MAX_CTX = 4096  # mets 8192 si ta VRAM le permet

modelsLlama = AutoAWQForCausalLM.from_quantized(
    LLAMA_AWQ_PATH,
    device_map={"": 0},
    fuse_layers=True,
    safetensors=True,
    max_seq_len=LLAMA_MAX_CTX,  # <-- clé !
    batch_size=1,               # optionnel, mais propre pour le cache KV
)
ensure_pad_token(tokenizer_Llama, modelsLlama)
try:
    modelsLlama.config.attn_implementation = "eager"
    modelsLlama.generation_config.attn_implementation = "eager"
except Exception:
    pass

try:
    from torch.backends.cuda import sdp_kernel
    sdp_kernel.enable_flash_sdp(False)
    sdp_kernel.enable_mem_efficient_sdp(False)  # <-- mets False
    sdp_kernel.enable_math_sdp(True)
except Exception:
    pass
modelsLlama.config.use_cache = True
print(f"✅ LLaMA3-8B (AWQ) chargé. VRAM utilisée : {torch.cuda.memory_allocated() / 1024**3:.2f} GB")
warmup_model(modelsLlama, tokenizer_Llama)


PROMPT_PREFIX_8B = """You are an expert data engineer specialized in writing optimized, precise, and executable SQL queries for Oracle 11g.

You will be given:
- A database schema.
- A user question (often in French). A [HINT: ...] may be appended.

Your task:
- Output one valid SQL query using ONLY the schema.
- If the question cannot be answered from the schema, reply exactly: SELECT 'Not possible';

Hard constraints:
- Oracle 11g syntax only. Keep identifiers EXACTLY as in the schema.
- NEVER invent tables/columns/joins.

Column/table mapping (CRITICAL):
- “programme” → filter on PROGRAMME (never MISSION).
- “mission” → filter on MISSION (not PROGRAMME).
- “ministère/ministry” → MINISTERE.
- “crédit initial voté / LFI” → CREDITLFI.
- If the question mentions any “crédit*” (e.g., crédits payés/ordonnancés/engagés/disponibles), use VECTIS_SITUATION_BUDGET and NEVER use MONTANTOP/OP.
- Payments / “ordonnances/ordres de paiement” / “paiements” / NUMOP / MONTANTOP → VECTIS_SITUATION_OP only.

When both rules could apply, PRIORITY is:
1) Any “crédit*” term → VECTIS_SITUATION_BUDGET (budget aggregates).
2) Otherwise payment terms → VECTIS_SITUATION_OP.

Database schema:
"""


PROMPT_SUFFIX_8B = """

User question:
"""
PROMPT_SUFFIX2_8B = """

SQL Query:
"""


PRETOKENIZED_PROMPT_PREFIX_8B = tokenizer_sqlcoder_8b(
    PROMPT_PREFIX_8B,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")

PRETOKENIZED_SCHEMA_8B = tokenizer_sqlcoder_8b(
    SCHEMA_SQL_detaille_final,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")

PRETOKENIZED_PROMPT_SUFFIX_8B = tokenizer_sqlcoder_8b(
    PROMPT_SUFFIX_8B,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")

PRETOKENIZED_PROMPT_SUFFIX2_8B = tokenizer_sqlcoder_8b(
    PROMPT_SUFFIX2_8B,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")




PROMPT_PREFIX_LLAMA = """
You are an Oracle 11g expert and a public finance business analyst.

You receive:
- A user question in French (a [HINT: ...] may be appended).
- A detailed SQL schema.
- Two candidate SQL queries.

Your task:
1) Understand exactly what is asked (sum? aggregate? list?).
2) Review the candidates. Fix if needed (column names, aggregates, filters).
3) Prefer including relevant contextual columns when appropriate.
4) Select the best query, or write a new one if none fits. It must execute on Oracle 11g.

STRICT output:
- Return ONLY the final query wrapped by [SQL] and [/SQL]. One semicolon at the end.
- No explanations. Use ONLY schema tables/columns. No unnecessary joins.
- Keep identifiers exactly as in the schema.
- If a [HINT: ...] is present, FOLLOW IT strictly.

Disambiguation & mapping (MANDATORY):
- “programme” → PROGRAMME (never MISSION).
- “mission” → MISSION (not PROGRAMME).
- “ministère/ministry” → MINISTERE.
- “crédit initial voté / LFI” → CREDITLFI.
- If the question contains any “crédit*” (e.g., crédits payés/ordonnancés/engagés/disponibles), use VECTIS_SITUATION_BUDGET and NEVER use MONTANTOP/OP.
- Payments / “ordonnances/ordres de paiement” / “paiements” / NUMOP / MONTANTOP → use VECTIS_SITUATION_OP only.

Priority when both appear:
1) “crédit*” terms → choose VECTIS_SITUATION_BUDGET.
2) Otherwise, payment terms → choose VECTIS_SITUATION_OP.

Keep all explicit user filters (dates, ministries, programs, missions, titles, etc.) exactly as requested.

User question:
"""


PROMPT_SUFFIX1_LLAMA = """

Schéma SQL :
"""
PROMPT_SUFFIX2_LLAMA = """

Proposition SQL 1 :
"""
PROMPT_SUFFIX3_LLAMA = """

Proposition SQL 2 :
"""
PROMPT_SUFFIX4_LLAMA = """

Requête finale corrigée (encadrée) :
[SQL]
"""

PRETOKENIZED_PROMPT_PREFIX_LLAMA = tokenizer_Llama(
    PROMPT_PREFIX_LLAMA,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")

PRETOKENIZED_PROMPT_SUFFIX1_LLAMA = tokenizer_Llama(
    PROMPT_SUFFIX1_LLAMA,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")

PRETOKENIZED_SCHEMA_LLAMA = tokenizer_Llama(
    SCHEMA_SQL_detaille_final,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")

PRETOKENIZED_PROMPT_SUFFIX2_LLAMA = tokenizer_Llama(
    PROMPT_SUFFIX2_LLAMA,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")

PRETOKENIZED_PROMPT_SUFFIX3_LLAMA = tokenizer_Llama(
    PROMPT_SUFFIX3_LLAMA,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")

PRETOKENIZED_PROMPT_SUFFIX4_LLAMA = tokenizer_Llama(
    PROMPT_SUFFIX4_LLAMA,
    return_tensors="pt",
    add_special_tokens=False
)["input_ids"].to("cuda")



PRETOKENIZED_STATIC_PROMPT_8B = torch.cat([
    PRETOKENIZED_PROMPT_PREFIX_8B,
    PRETOKENIZED_SCHEMA_8B,
    PRETOKENIZED_PROMPT_SUFFIX_8B
], dim=1)