import os
from dotenv import load_dotenv
import os.path as op

load_dotenv(override=True)

def _sanitize_env_path(p: str | None) -> str:
    if not p:
        return ""
    p = p.strip()
    if (p.startswith('r"') and p.endswith('"')) or (p.startswith("r'") and p.endswith("'")):
        p = p[2:-1]
    if (p.startswith('"') and p.endswith('"')) or (p.startswith("'") and p.endswith("'")):
        p = p[1:-1]
    return op.normpath(p)

def _to_float(x, default):
    try: return float(x)
    except Exception: return default

def _to_int(x, default):
    try: return int(x)
    except Exception: return default

# === Provider / contact ===
MAPS_PROVIDER = os.getenv("MAPS_PROVIDER", "osm").strip().lower()
CONTACT_EMAIL = os.getenv("CONTACT_EMAIL", "").strip()

# === Google (optionnel) ===
GOOGLE_MAPS_API_KEY = os.getenv("GOOGLE_MAPS_API_KEY", "").strip()

# === Defaults Tanger ===
DEFAULT_LAT = _to_float(os.getenv("DEFAULT_LAT", "35.7847"), 35.7847)
DEFAULT_LNG = _to_float(os.getenv("DEFAULT_LNG", "-5.8129"), -5.8129)
SEARCH_RADIUS_METERS = _to_int(os.getenv("SEARCH_RADIUS_METERS", "1800"), 1800)

# === LLaMA AWQ (chemin local) ===
LLAMA_AWQ_PATH = _sanitize_env_path(os.getenv("LLAMA_AWQ_PATH"))

# === Budgets génération ===
MAX_NEW_TOK_CLASSIFY = _to_int(os.getenv("MAX_NEW_TOK_CLASSIFY", "128"), 128)
MAX_NEW_TOK_ANSWER   = _to_int(os.getenv("MAX_NEW_TOK_ANSWER", "384"), 384)

# === Robustesse JSON & logs ===
MAX_JSON_FIX_ATTEMPTS = _to_int(os.getenv("MAX_JSON_FIX_ATTEMPTS", "6"), 6)
VERBOSE_LOG = os.getenv("VERBOSE_LOG", "1").strip().lower() not in ("0", "false", "no")

# === Overpass / HTTP timeouts ===
OVERPASS_SERVER_TIMEOUT_S = _to_int(os.getenv("OVERPASS_SERVER_TIMEOUT_S", "20"), 20)
HTTP_TIMEOUT_S            = _to_int(os.getenv("HTTP_TIMEOUT_S", "12"), 12)
OVERPASS_MAX_STEPS        = _to_int(os.getenv("OVERPASS_MAX_STEPS", "3"), 3)

# === Hints ===
CITY_HINT = os.getenv("CITY_HINT", "Tanger, Maroc").strip()
