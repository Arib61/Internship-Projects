import json
import math
import re
from typing import Optional

# --- géo ---
def haversine_m(lat1, lon1, lat2, lon2):
    R = 6371000.0
    p1, p2 = math.radians(lat1), math.radians(lat2)
    dphi = math.radians(lat2 - lat1)
    dlmb = math.radians(lon2 - lon1)
    a = math.sin(dphi/2)**2 + math.cos(p1)*math.cos(p2)*math.sin(dlmb/2)**2
    return 2*R*math.asin(math.sqrt(a))

def gm_maps_url_from_place(place_id: str):
    return f"https://www.google.com/maps/place/?q=place_id:{place_id}"

# --- LLM JSON helpers ---
_JSON_TAG_RE = re.compile(r"<json>(.*?)</json>", re.DOTALL | re.IGNORECASE)

def extract_json_block(text: str) -> str:
    if not text: return ""
    m = _JSON_TAG_RE.search(text)
    return m.group(1) if m else text

def safe_json_parse(s: str) -> Optional[dict]:
    try:
        return json.loads(s)
    except Exception:
        return None

def try_fix_json(s: str) -> str:
    # petites corrections usuelles
    s = s.strip()
    # fermer guillemets manquants après selected_place_ids/names
    s = re.sub(r",\s*}", "}", s)
    s = re.sub(r",\s*]", "]", s)
    # retirer trailing text après le dernier }
    last = s.rfind("}")
    if last != -1:
        s = s[:last+1]
    return s

def normalize_name(x: str) -> str:
    return re.sub(r"\s+", " ", (x or "").strip().lower())
