import os
import torch
from transformers import AutoTokenizer, logging
from awq import AutoAWQForCausalLM
from config import LLAMA_AWQ_PATH, MAX_NEW_TOK_CLASSIFY, MAX_NEW_TOK_ANSWER, VERBOSE_LOG

os.environ.setdefault("TRANSFORMERS_OFFLINE", "1")
os.environ.setdefault("HF_HUB_OFFLINE", "1")
logging.set_verbosity_error()

torch.backends.cuda.matmul.allow_tf32 = True
torch.backends.cudnn.allow_tf32 = True
try:
    from torch.backends.cuda import sdp_kernel
    sdp_kernel.enable_flash_sdp(False)
    sdp_kernel.enable_mem_efficient_sdp(True)
    sdp_kernel.enable_math_sdp(True)
except Exception:
    pass

tokenizer = AutoTokenizer.from_pretrained(LLAMA_AWQ_PATH, use_fast=True, local_files_only=True)
model = AutoAWQForCausalLM.from_quantized(
    LLAMA_AWQ_PATH,
    device_map="auto",
    fuse_layers=True,
    safetensors=True,
)
if tokenizer.pad_token is None:
    tokenizer.pad_token = tokenizer.eos_token
model.config.pad_token_id = tokenizer.pad_token_id
model.config.use_cache = True
try:
    model.config.attn_implementation = "sdpa"
except Exception:
    pass

device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
model.to(device)

_ = model.generate(**tokenizer("ok", return_tensors="pt").to(device), max_new_tokens=2)

if VERBOSE_LOG and torch.cuda.is_available():
    used = torch.cuda.memory_allocated() / (1024**3)
    print(f"✅ LLaMA AWQ chargé. VRAM ~{used:.2f} GB")

def _generate(prompt: str, max_new_tokens: int):
    enc = tokenizer(prompt, return_tensors="pt").to(device)
    prompt_len = enc["input_ids"].shape[1]
    with torch.inference_mode():
        out = model.generate(
            **enc,
            do_sample=False, temperature=0.0, top_p=1.0, num_beams=1,
            max_new_tokens=max_new_tokens,
            eos_token_id=tokenizer.eos_token_id,
            pad_token_id=tokenizer.pad_token_id,
            use_cache=True
        )
    gen_only = out[0, prompt_len:]
    return tokenizer.decode(gen_only, skip_special_tokens=True)

JSON_PREFIX = (
    "Tu dois répondre UNIQUEMENT en JSON strict. "
    "N'inclus AUCUN autre texte. Place ta réponse entre <json> et </json>.\n"
)

def _wrap_force_json(base_prompt: str, schema_hint: str | None = None) -> str:
    hint = f"\nSchéma attendu (exemple) : {schema_hint}\n" if schema_hint else "\n"
    return f"{JSON_PREFIX}{hint}{base_prompt}\nRéponds uniquement entre <json> et </json>."

def generate_text(prompt: str, max_new_tokens: int):
    return _generate(prompt, max_new_tokens=max_new_tokens)

def classify_intents_and_entities(prompt: str):
    schema = (
        '{"intent":"restaurant|monument|horaire|itineraire|traduction|autre",'
        '"entities":{"cuisine":null,"budget":null,"indoor_outdoor":null,'
        '"time_ref":null,"place_hint":null,"route_target":null,"opening_subject":null},'
        '"needs_location":true}'
    )
    return _generate(_wrap_force_json(prompt, schema), max_new_tokens=MAX_NEW_TOK_CLASSIFY)

def write_final_answer(prompt: str):
    return _generate(prompt, max_new_tokens=MAX_NEW_TOK_ANSWER)

def write_json_answer(prompt: str, schema_hint: str | None = None):
    return _generate(_wrap_force_json(prompt, schema_hint), max_new_tokens=MAX_NEW_TOK_ANSWER)
