import requests
from typing import List, Dict, Optional, Tuple
import certifi
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

from config import GOOGLE_MAPS_API_KEY, SEARCH_RADIUS_METERS
from utils import haversine_m, gm_maps_url_from_place

BASE = "https://maps.googleapis.com/maps/api"
PLACES_NEARBY  = f"{BASE}/place/nearbysearch/json"
PLACES_TEXT    = f"{BASE}/place/textsearch/json"
PLACE_DETAILS  = f"{BASE}/place/details/json"
DIRECTIONS     = f"{BASE}/directions/json"
GEOCODE        = f"{BASE}/geocode/json"

VERIFY_PATH = certifi.where()
DEFAULT_TIMEOUT = 15

SESSION = requests.Session()
retries = Retry(total=3, backoff_factor=0.6, status_forcelist=(429, 500, 502, 503, 504),
                allowed_methods=frozenset(["GET"]))
adapter = HTTPAdapter(max_retries=retries)
SESSION.mount("https://", adapter)
SESSION.mount("http://", adapter)

def _get_json(url: str, params: dict) -> dict:
    r = SESSION.get(url, params=params, timeout=DEFAULT_TIMEOUT, verify=VERIFY_PATH)
    r.raise_for_status()
    return r.json() or {}

def geocode_text(query: str) -> Optional[Tuple[float, float]]:
    if not GOOGLE_MAPS_API_KEY or not query:
        return None
    params = {"address": query, "key": GOOGLE_MAPS_API_KEY}
    j = _get_json(GEOCODE, params)
    if (j.get("status") == "OK") and j.get("results"):
        loc = j["results"][0]["geometry"]["location"]
        return float(loc["lat"]), float(loc["lng"])
    return None

def text_search(query: str, lat: float, lng: float, radius: int = SEARCH_RADIUS_METERS) -> dict:
    if not GOOGLE_MAPS_API_KEY:
        return {}
    params = {"query": query, "location": f"{lat},{lng}", "radius": radius, "key": GOOGLE_MAPS_API_KEY}
    return _get_json(PLACES_TEXT, params)

def nearby_search(lat: float, lng: float, radius: int, type_: Optional[str] = None, keyword: Optional[str] = None) -> dict:
    if not GOOGLE_MAPS_API_KEY:
        return {}
    params = {"location": f"{lat},{lng}", "radius": radius, "key": GOOGLE_MAPS_API_KEY}
    if type_: params["type"] = type_
    if keyword: params["keyword"] = keyword
    return _get_json(PLACES_NEARBY, params)

def place_details(place_id: str) -> dict:
    if not GOOGLE_MAPS_API_KEY or not place_id: return {}
    params = {
        "place_id": place_id,
        "key": GOOGLE_MAPS_API_KEY,
        "fields": "place_id,name,geometry/location,rating,user_ratings_total,price_level,opening_hours,formatted_address,types,website"
    }
    return _get_json(PLACE_DETAILS, params)

def directions_between(origin: Tuple[float, float], dest: Tuple[float, float], mode: str = "walking") -> dict:
    if not GOOGLE_MAPS_API_KEY: return {}
    params = {"origin": f"{origin[0]},{origin[1]}", "destination": f"{dest[0]},{dest[1]}", "mode": mode, "key": GOOGLE_MAPS_API_KEY}
    return _get_json(DIRECTIONS, params)

def normalize_poi_list(raw_results: dict, origin_lat: float, origin_lng: float) -> List[Dict]:
    out = []
    results = (raw_results or {}).get("results") or []
    for r in results:
        pid = r.get("place_id"); name = r.get("name")
        loc = r.get("geometry", {}).get("location", {})
        plat, plng = loc.get("lat"), loc.get("lng")
        if not pid or plat is None or plng is None: continue
        dist_m = haversine_m(origin_lat, origin_lng, float(plat), float(plng))
        out.append({
            "place_id": pid, "name": name, "lat": float(plat), "lng": float(plng),
            "rating": r.get("rating"), "user_ratings_total": r.get("user_ratings_total"),
            "price_level": r.get("price_level"), "types": r.get("types", []),
            "distance_m": round(dist_m, 1), "maps_url": gm_maps_url_from_place(pid)
        })
    return out

def enrich_with_details(pois: List[Dict], max_details: int = 15) -> List[Dict]:
    enriched = []
    for p in pois[:max_details]:
        det = place_details(p["place_id"]) or {}
        result = det.get("result") or {}
        oh = result.get("opening_hours") or {}
        enriched.append({
            **p, "formatted_address": result.get("formatted_address"),
            "opening_hours": {"open_now": oh.get("open_now"), "weekday_text": oh.get("weekday_text")},
            "website": result.get("website")
        })
    enriched.extend(pois[max_details:])
    return enriched
