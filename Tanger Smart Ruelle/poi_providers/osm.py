from typing import List, Dict, Optional, Tuple
import time
import requests
import certifi
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry
from cachetools import TTLCache
import os, certifi


from config import (
    SEARCH_RADIUS_METERS, CONTACT_EMAIL,
    OVERPASS_SERVER_TIMEOUT_S, HTTP_TIMEOUT_S, OVERPASS_MAX_STEPS
)
from utils import haversine_m

NOMINATIM = "https://nominatim.openstreetmap.org/search"
OVERPASS  = "https://overpass-api.de/api/interpreter"
OSRM      = "https://router.project-osrm.org/route/v1"

import os
HTTP_VERIFY = os.getenv("HTTP_VERIFY", "1").lower() not in ("0","false","no")
VERIFY_PATH = certifi.where() if HTTP_VERIFY else False

DEFAULT_TIMEOUT = HTTP_TIMEOUT_S
def _resolve_verify():
    v = (os.getenv("HTTP_VERIFY", "certifi") or "").strip().lower()
    if v in ("0", "false", "no"): return False
    if v in ("1", "true", "yes", "certifi"): return certifi.where()
    return v  # chemin vers un .pem custom
VERIFY_PATH = _resolve_verify()

SESSION = requests.Session()
retries = Retry(
    total=3, backoff_factor=0.6,
    status_forcelist=(429, 500, 502, 503, 504),
    allowed_methods=frozenset(["GET", "POST"])
)
adapter = HTTPAdapter(max_retries=retries, pool_connections=50, pool_maxsize=50)
SESSION.mount("https://", adapter)
SESSION.mount("http://", adapter)

def _headers():
    ua = "TangerSmartRuelle/1.3"
    if CONTACT_EMAIL:
        ua += f" ({CONTACT_EMAIL})"
    return {"User-Agent": ua}

def _get(url: str, params: dict | None = None):
    try:
        r = SESSION.get(url, params=params or {}, headers=_headers(),
                        timeout=DEFAULT_TIMEOUT, verify=VERIFY_PATH)
    except requests.exceptions.SSLError:
        # Fallback DEV si certif casse : on réessaie sans vérification
        if VERIFY_PATH is not False:
            r = SESSION.get(url, params=params or {}, headers=_headers(),
                            timeout=DEFAULT_TIMEOUT, verify=False)
        else:
            raise
    r.raise_for_status()
    return r

def _post(url: str, data: dict | None = None):
    try:
        r = SESSION.post(url, data=data or {}, headers=_headers(),
                         timeout=DEFAULT_TIMEOUT, verify=VERIFY_PATH)
    except requests.exceptions.SSLError:
        if VERIFY_PATH is not False:
            r = SESSION.post(url, data=data or {}, headers=_headers(),
                             timeout=DEFAULT_TIMEOUT, verify=False)
        else:
            raise
    r.raise_for_status()
    return r

_overpass_cache = TTLCache(maxsize=1024, ttl=300)
_nominatim_cache = TTLCache(maxsize=512, ttl=900)

def _osm_maps_url(lat: float, lng: float, osm_type: Optional[str] = None, osm_id: Optional[int] = None):
    if osm_type and osm_id:
        return f"https://www.openstreetmap.org/{osm_type}/{osm_id}"
    return f"https://www.openstreetmap.org/?mlat={lat}&mlon={lng}#map=17/{lat}/{lng}"

def _osrm_route_url(lat1: float, lng1: float, lat2: float, lng2: float):
    return f"https://www.openstreetmap.org/directions?engine=fossgis_osrm_foot&route={lat1},{lng1};{lat2},{lng2}"

def geocode_text(query: str) -> Optional[Tuple[float, float]]:
    if not query:
        return None
    key = query.strip().lower()
    if key in _nominatim_cache:
        return _nominatim_cache[key]
    params = {"q": query, "format": "jsonv2", "limit": 1}
    try:
        j = _get(NOMINATIM, params=params).json()
        if j:
            pt = (float(j[0]["lat"]), float(j[0]["lon"]))
            _nominatim_cache[key] = pt
            return pt
    except Exception:
        pass
    return None

def _overpass_query_amenities(lat: float, lng: float, radius: int, filters: List[str]) -> dict:
    around = f"(around:{radius},{lat},{lng})"
    flt_block = "\n".join([f'node{around}{f};\nway{around}{f};\nrelation{around}{f};' for f in filters])
    q = f"""
    [out:json][timeout:{OVERPASS_SERVER_TIMEOUT_S}];
    ( {flt_block} );
    out center 60;
    """.strip()
    cached = _overpass_cache.get(q)
    if cached is not None:
        return cached
    try:
        res = _post(OVERPASS, data={"data": q}).json()
    except Exception:
        res = {"elements": []}
    _overpass_cache[q] = res
    return res

def _elements_to_pois(elements: List[dict], origin_lat: float, origin_lng: float) -> List[Dict]:
    pois = []
    for el in elements:
        tags = el.get("tags", {}) or {}
        name = tags.get("name")
        if "lat" in el and "lon" in el:
            plat, plng = el["lat"], el["lon"]
        else:
            c = el.get("center")
            if not c:
                continue
            plat, plng = c.get("lat"), c.get("lon")
        if plat is None or plng is None:
            continue
        dist_m = haversine_m(origin_lat, origin_lng, float(plat), float(plng))
        osm_type = el.get("type"); osm_id = el.get("id")
        opening_raw = tags.get("opening_hours")
        opening_dict = {"open_now": None, "weekday_text": [opening_raw] if opening_raw else None}
        pois.append({
            "place_id": f"osm:{osm_type}:{osm_id}",
            "name": name or tags.get("amenity") or tags.get("tourism") or tags.get("historic") or "Lieu OSM",
            "lat": float(plat), "lng": float(plng),
            "rating": None, "user_ratings_total": None, "price_level": None,
            "types": [t for t in [tags.get("amenity"), tags.get("tourism"), tags.get("historic")] if t],
            "distance_m": round(dist_m, 1),
            "maps_url": _osm_maps_url(float(plat), float(plng), osm_type, osm_id),
            "formatted_address": None,
            "opening_hours": opening_dict,
            "website": tags.get("website") or tags.get("contact:website")
        })
    pois.sort(key=lambda x: x["distance_m"])
    return pois

def _restaurant_filters(keyword: Optional[str]) -> List[str]:
    if not keyword:
        return ['["amenity"="restaurant"]']
    kw = (keyword or "").replace('"', '')
    return [
        f'["amenity"="restaurant"]["name"~"{kw}",i]',
        f'["amenity"="restaurant"]["cuisine"~"{kw}",i]',
        '["amenity"="restaurant"]["cuisine"~"fish|seafood|poisson|grill|grilled|grillade|moroccan|marocain|marocaine|couscous",i]'
    ]

def _cafe_filters(keyword: Optional[str]) -> List[str]:
    if not keyword:
        return [
            '["amenity"="cafe"]',
            '["shop"="coffee"]'
        ]
    kw = (keyword or "").replace('"', '')
    return [
        f'["amenity"="cafe"]["name"~"{kw}",i]',
        f'["amenity"="cafe"]["cuisine"~"{kw}",i]',
        f'["shop"="coffee"]["name"~"{kw}",i]'
    ]


def nearby_search(lat: float, lng: float, radius: int, type_: Optional[str] = None, keyword: Optional[str] = None) -> dict:
    if type_ == "restaurant":
        filters = _restaurant_filters(keyword)
    elif type_ == "cafe":
        filters = _cafe_filters(keyword)
    elif type_ == "tourist_attraction":
        filters = [
            '["tourism"="attraction"]',
            '["historic"="monument"]',
            '["historic"="castle"]',
            '["tourism"="museum"]',
            '["tourism"="artwork"]',
            '["tourism"="viewpoint"]'
        ]
    elif type_ == "point_of_interest":
        filters = ['["amenity"]', '["tourism"]', '["historic"]']
    else:
        filters = ['["amenity"]', '["tourism"]', '["historic"]']

    steps = max(1, min(OVERPASS_MAX_STEPS, 3))
    radii = [radius]
    if steps >= 2:
        radii.append(min(int(radius * 1.5), 2200))
    if steps >= 3:
        radii.append(3200)

    last = {"elements": []}
    for r in radii:
        j = _overpass_query_amenities(lat, lng, r, filters)
        last = j
        if j.get("elements"):
            return j
        if type_ == "restaurant" and keyword:
            j2 = _overpass_query_amenities(lat, lng, r, _restaurant_filters(None))
            if j2.get("elements"):
                return j2
        time.sleep(0.1)
    return last

def text_search(query: str, lat: float, lng: float, radius: int = SEARCH_RADIUS_METERS) -> dict:
    if not query:
        return {"elements": []}
    q = f"""
    [out:json][timeout:{OVERPASS_SERVER_TIMEOUT_S}];
    (
      node(around:{radius},{lat},{lng})["name"~"{query}",i];
      way(around:{radius},{lat},{lng})["name"~"{query}",i];
      relation(around:{radius},{lat},{lng})["name"~"{query}",i];
    );
    out center 50;
    """.strip()
    cached = _overpass_cache.get(q)
    if cached is not None:
        return cached
    try:
        res = _post(OVERPASS, data={"data": q}).json()
    except Exception:
        res = {"elements": []}
    _overpass_cache[q] = res
    return res

def place_details(place_id: str) -> dict:
    return {"result": {}}

def directions_between(origin: Tuple[float, float], dest: Tuple[float, float], mode: str = "walking") -> dict:
    profile = {"walking": "foot", "driving": "driving", "bicycling": "bike"}.get(mode, "foot")
    url = f"{OSRM}/{profile}/{origin[1]},{origin[0]};{dest[1]},{dest[0]}?overview=false&steps=false"
    try:
        j = _get(url).json()
    except Exception:
        j = {}
    j["route_url"] = _osrm_route_url(origin[0], origin[1], dest[0], dest[1])
    return j

def normalize_poi_list(raw_results: dict, origin_lat: float, origin_lng: float) -> List[Dict]:
    elements = (raw_results or {}).get("elements") or []
    return _elements_to_pois(elements, origin_lat, origin_lng)

def enrich_with_details(pois: List[Dict], max_details: int = 15) -> List[Dict]:
    return pois
