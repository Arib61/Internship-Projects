# router.py
from typing import Dict, List, Tuple
from intents import Intent
from config import MAPS_PROVIDER, SEARCH_RADIUS_METERS, VERBOSE_LOG  # ⬅️ ajoute VERBOSE_LOG

if MAPS_PROVIDER == "google":
    from poi_providers.google_maps import (
        geocode_text, text_search, nearby_search,
        normalize_poi_list, enrich_with_details, directions_between
    )
else:
    from poi_providers.osm import (
        geocode_text, text_search, nearby_search,
        normalize_poi_list, enrich_with_details, directions_between
    )

def _dedupe_by_place_id(pois: List[Dict]) -> List[Dict]:
    seen, out = set(), []
    for p in pois:
        pid = p.get("place_id")
        if pid and pid not in seen:
            seen.add(pid); out.append(p)
    return out

def fetch_candidates(intent: str, origin: Tuple[float, float], special_location: str = None, entities: Dict = None) -> List[Dict]:
    lat, lng = origin
    cuisine = (entities or {}).get("cuisine")
    hint = (entities or {}).get("place_hint") or special_location

    if intent == Intent.RESTAURANT.value:
        pois: List[Dict] = []

        # 1) Autour : cafés + restaurants
        for typ in ("cafe", "restaurant"):
            raw = nearby_search(lat, lng, SEARCH_RADIUS_METERS, type_=typ, keyword=cuisine)
            r = normalize_poi_list(raw, lat, lng)
            if VERBOSE_LOG: print(f"[POI] origin nearby {typ}: {len(r)}")
            pois += r

        pois = _dedupe_by_place_id(pois)

        # 2) Si pas assez de candidats ET un hint (ex: "Marina"), on complète en text_search
        if len(pois) < 10 and hint:
            tx = text_search(
                f"{hint} (cafe|café|coffee|espresso|salon de thé|coffee shop)",
                lat, lng, int(SEARCH_RADIUS_METERS * 1.4)
            )
            tr = normalize_poi_list(tx, lat, lng)
            if VERBOSE_LOG:
                print(f"[POI] after text_search: {len(tr)} (center={origin}, hint='{hint}')")
            pois += tr
            pois = _dedupe_by_place_id(pois)

        # 3) Tri léger (distance puis note)
        pois.sort(key=lambda x: (x.get("distance_m") or 1e9, -(x.get("rating") or 0), -(x.get("user_ratings_total") or 0)))

        # 4) Détails (Google: horaires, etc. / OSM: passthrough)
        pois = enrich_with_details(pois[:24])
        return pois

    if intent == Intent.MONUMENT.value:
        raw = nearby_search(lat, lng, SEARCH_RADIUS_METERS, type_="tourist_attraction")
        pois = normalize_poi_list(raw, lat, lng)
        if hint:
            tx = text_search(f"{hint}|kasbah|médina|medina|musée|fort|castle|monument", lat, lng, SEARCH_RADIUS_METERS)
            pois += normalize_poi_list(tx, lat, lng)
        pois = _dedupe_by_place_id(pois)
        pois = enrich_with_details(pois[:24])
        return pois

    if intent == Intent.HORAIRE.value:
        raw = nearby_search(lat, lng, SEARCH_RADIUS_METERS, type_="point_of_interest")
        pois = normalize_poi_list(raw, lat, lng)
        pois = enrich_with_details(pois[:30])
        return pois

    return []

def route_for_selection(origin: Tuple[float, float], selection: Dict, mode: str = "walking"):
    dest = (selection["lat"], selection["lng"])
    dj = directions_between(origin, dest, mode=mode)
    route_url = dj.get("route_url") or f"https://www.google.com/maps/dir/?api=1&origin={origin[0]},{origin[1]}&destination={dest[0]},{dest[1]}&travelmode={mode}"
    return {"directions_api_raw": dj, "route_url": route_url}
