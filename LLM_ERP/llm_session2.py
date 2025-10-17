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


PROMPT_PREFIX_8B = """You are an expert data engineer specialized in writing optimized, precise, and executable SQL queries.

You will be given:
- A database schema with tables and columns.
- A user question expressed in natural language.

Your task:
- Translate the user question into a valid, optimized, and accurate SQL query.
- The SQL query must ONLY use the tables and columns defined in the provided schema.
- If the question cannot be answered based on the provided schema, reply with exactly:
SELECT 'Not possible';

Constraints:
- Do NOT invent any tables, columns, or relationships that are not explicitly defined in the schema.
- Use appropriate SQL clauses (e.g. WHERE, GROUP BY, ORDER BY) when necessary.
- Ensure the SQL is syntactically correct and ready to execute.

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
Tu es un expert Oracle 11g et analyste métier en finances publiques.

Tu reçois :
- Une question utilisateur en français
- Un schéma SQL détaillé (tables, colonnes, types, descriptions)
- Deux propositions de requêtes SQL candidates

Ta tâche :
1. Lis la question et déduis précisément la demande (faut-il une somme ? une agrégation ? une simple liste ?).
2. Analyse les deux requêtes. Corrige-les si nécessaire (nom de colonne, agrégation manquante, etc).
3. Essaie vraiment d'inclut les colonnes pertienents souvant la proposition 2 donne des colonne pertinents
4. Sélectionne la meilleure requête, ou écris-en une nouvelle si aucune ne convient. Elle doit être directement exécutable sur Oracle 11g.

**Instructions de sortie :**
- Retourne UNIQUEMENT la requête finale, encadrée par [SQL] et [/SQL] (exemple : [SQL] SELECT SUM(MONTANT) FROM ... ; [/SQL]).
- Un seul point-virgule à la fin.
- Aucune explication, aucun commentaire, aucun code fence, rien en dehors des balises.
- La requête doit utiliser SEULEMENT des colonnes et tables présentes dans le schéma fourni.
- Inclut les colonnes pertienents souvant la proposition 2 donne des colonne pertinents
- Lorsqu’une colonne existe à la fois dans plusieurs tables du schéma (VECTIS_SITUATION_OP et VECTIS_SITUATION_BUDGET), choisir celle qui est la plus pertinente par rapport à ce que veut l’utilisateur.
- Si la question parle d’ordonnances / ordres de paiement / paiements / “payé(s)” / NUMOP / MONTANTOP ➜ UTILISER exclusivement la table VECTIS_SITUATION_OP.
- Si la question parle de crédits, taux, LFI/LFR, “crédit engagé/ordonnancé/payé/disponible,...(autre type de crédit)”, agrégats budgétaires ➜ UTILISER VECTIS_SITUATION_BUDGET.
- N'oublie aucun filtre demandé par l'utilisateur
- N'ajoute aucun jointure inutile
- Si la question parle de programme utlise la colonne PROGRAMME.
- Si la question parle de mission utilise la colonne MISSION.

Question utilisateur :
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