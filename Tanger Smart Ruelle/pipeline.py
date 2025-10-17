import json
from typing import Dict, Tuple, Optional, List
from difflib import get_close_matches
from time import perf_counter
import re

from intents import Intent
from config import DEFAULT_LAT, DEFAULT_LNG, MAX_JSON_FIX_ATTEMPTS, VERBOSE_LOG, CITY_HINT
from llm_session import classify_intents_and_entities, write_final_answer, write_json_answer
from prompts import (
    classify_and_extract_prompt, ranking_and_answer_prompt, opening_hours_answer_prompt, translation_prompt,
    no_poi_fallback_prompt, chitchat_prompt
)
from router import fetch_candidates, route_for_selection
from router import geocode_text, text_search, normalize_poi_list, enrich_with_details
from utils import extract_json_block, try_fix_json, safe_json_parse, normalize_name

# --- chitchat fast-path ---
_CHITCHAT_SIMPLE = {
    "salut", "slt", "bonjour", "bonsoir", "coucou",
    "hello", "hi", "hey",
    "salam", "slm", "salam alaykoum", "salam alaikoum",
    "merci", "thanks", "thank you", "choukran", "shukran",
    "ok", "d'accord", "dac", "ça va", "ca va"
}
def _looks_like_chitchat(q: str) -> bool:
    if not q: return True
    qn = q.strip().lower()
    if qn in _CHITCHAT_SIMPLE: return True
    if len(qn) <= 16 and re.match(r"^(salut|bonjour|bonsoir|hello|hi|hey|coucou|salam)\b", qn):
        return True
    if re.search(r"\b(merci|thanks|choukran|shukran|ok|d'accord|ça va|ca va)\b", qn):
        tokens = re.findall(r"\w+", qn)
        return len(tokens) <= 4
    return False

# --- hint aliases ---
PLACE_HINT_ALIASES = {
    "marina": "Tanja Marina Bay International",
    "marina tanger": "Tanja Marina Bay International",
    "tanja marina": "Tanja Marina Bay International",
    "marina bay": "Tanja Marina Bay International",
}
def _normalize_hint(h: Optional[str]) -> str:
    if not h: return h
    return PLACE_HINT_ALIASES.get(h.strip().lower(), h)

def _origin_from_inputs(lat, lng) -> Tuple[float, float]:
    try:
        return (float(lat), float(lng)) if (lat is not None and lng is not None) else (DEFAULT_LAT, DEFAULT_LNG)
    except Exception:
        return (DEFAULT_LAT, DEFAULT_LNG)

def _parse_llm_json(text: str, no_raise: bool = False) -> Dict:
    block = extract_json_block(text)
    js = safe_json_parse(block)
    if js is not None:
        return js
    for _ in range(MAX_JSON_FIX_ATTEMPTS):
        block = try_fix_json(block)
        js = safe_json_parse(block)
        if js is not None:
            return js
    return {} if no_raise else {"_error": "Réponse LLM non-JSON"}

def _build_poi_cards(pois: List[Dict]) -> str:
    if not pois: return ""
    lines = []
    for p in pois[:12]:
        wd = p.get("opening_hours", {}).get("weekday_text")
        wd_txt = ("; ".join(wd) if wd else "horaires inconnus")
        lines.append(
            f"- id={p['place_id']} | {p['name']} | dist≈{p['distance_m']}m | note={p.get('rating', 'n/a')} "
            f"| avis={p.get('user_ratings_total', 'n/a')} | prix={p.get('price_level', 'n/a')} | {wd_txt}"
        )
    return "\n".join(lines)

def _location_note(origin: Tuple[float, float], special_location: Optional[str]):
    if special_location:
        return f"Localisation ciblée: {special_location} (sinon origine GPS lat={origin[0]:.5f}, lng={origin[1]:.5f})"
    return f"Origine GPS approx: lat={origin[0]:.5f}, lng={origin[1]:.5f}"

def _classify(question: str, lang: str, intent_hint: Optional[str]) -> Dict:
    if _looks_like_chitchat(question or ""):
        return {"intent": Intent.AUTRE.value, "entities": {}, "needs_location": False}

    from prompts import classify_force_json_prompt
    raw = classify_intents_and_entities(classify_and_extract_prompt(question, lang=lang))
    if VERBOSE_LOG: print("🔎 RAW classify:", raw)
    parsed = _parse_llm_json(raw, no_raise=True)
    if not parsed:
        raw2 = classify_intents_and_entities(classify_force_json_prompt(question, lang=lang))
        parsed = _parse_llm_json(raw2, no_raise=True)
    if not parsed:
        q = (question or "").lower()
        def pick_intent():
            if any(k in q for k in ["aller", "itinéraire", "route", "chemin", "comment aller"]):
                return Intent.ITINERAIRE.value
            if any(k in q for k in ["heure", "ouvert", "ferme", "horaire"]):
                return Intent.HORAIRE.value
            if any(k in q for k in ["monument", "musée", "kasbah", "porte", "site", "visiter"]):
                return Intent.MONUMENT.value
            if any(k in q for k in ["restaurant", "manger", "couscous", "poisson", "tajine", "pas cher", "grillé", "grille", "café", "cafe"]):
                return Intent.RESTAURANT.value
            if "comment dit-on" in q or "traduire" in q or "dire en arabe" in q:
                return Intent.TRADUCTION.value
            return Intent.AUTRE.value
        def extract_entities():
            ent = {"cuisine": None, "budget": None, "indoor_outdoor": None,
                   "time_ref": None, "place_hint": None, "route_target": None, "opening_subject": None}
            for kw in ["couscous", "poisson", "grillade", "grillé", "tajine", "pâtisserie", "café", "cafe", "paella", "pizza", "pastilla"]:
                if kw in q: ent["cuisine"] = kw; break
            for b in ["pas cher", "bon marché", "moyen", "cher"]:
                if b in q: ent["budget"] = b; break
            for loc in ["kasbah", "marina", "medina", "médina", "gare", "port", "centro", "centre"]:
                if loc in q: ent["place_hint"] = loc; break
            if any(k in q for k in ["heure", "ouvert", "ferme", "horaire"]):
                ent["opening_subject"] = question
            if any(k in q for k in ["aller", "route", "itinéraire"]):
                ent["route_target"] = question
            return ent
        parsed = {"intent": pick_intent(), "entities": extract_entities(), "needs_location": True}
    if intent_hint and intent_hint in parsed.get("intent", ""):
        parsed["intent"] = intent_hint
    return parsed

def _schema_hint_for_intent(intent: str) -> str:
    if intent in (Intent.RESTAURANT.value, Intent.MONUMENT.value, Intent.ITINERAIRE.value):
        return (
            '{"explanation":"...",'
            '"route_needed":true,'
            '"selected_place_ids":["id-from-cards","..."],'
            '"selected_place_names":["Name as in cards","..."]}'
        )
    if intent == Intent.HORAIRE.value:
        return (
            '{"explanation":"...",'
            '"best_match_place_id":"id-or-null",'
            '"best_match_place_name":"name-or-null",'
            '"is_open_now":true,'
            '"today_hours":"10:00-18:00"}'
        )
    return '{"explanation":"..."}'

from difflib import get_close_matches
from router import text_search, normalize_poi_list, enrich_with_details
from utils import normalize_name

def _match_selection(sel_ids: List[str], sel_names: List[str], pois: List[Dict], origin: Tuple[float, float]) -> List[Dict]:
    id_map = {p["place_id"]: p for p in pois}
    picked: List[Dict] = []

    # 1) ID exact
    for sid in sel_ids or []:
        if sid in id_map and id_map[sid] not in picked:
            picked.append(id_map[sid])

    # 2) ID OSM tronqué (ex: "5908580185" au lieu de "osm:node:5908580185")
    if len(picked) < 3 and sel_ids:
        numeric_ids = [sid for sid in sel_ids if sid.isdigit()]
        if numeric_ids:
            for nid in numeric_ids:
                for full_id, p in id_map.items():
                    if full_id.endswith(":" + nid) and p not in picked:
                        picked.append(p)
                        break

    # 3) Nom exact / fuzzy
    poi_name_map = {normalize_name(p.get("name", "")): p for p in pois if p.get("name")}
    for nm in sel_names or []:
        key = normalize_name(nm)
        if key in poi_name_map and poi_name_map[key] not in picked:
            picked.append(poi_name_map[key])
        else:
            choices = list(poi_name_map.keys())
            m = get_close_matches(key, choices, n=1, cutoff=0.82)
            if m:
                cand = poi_name_map[m[0]]
                if cand not in picked:
                    picked.append(cand)

    # 4) Dernière chance : petite recherche texte locale (si rien trouvé)
    if not picked and sel_names:
        lat, lng = origin
        for nm in sel_names[:3]:
            tx = text_search(nm, lat, lng)
            found = normalize_poi_list(tx, lat, lng)
            if found:
                ff = enrich_with_details(found[:1])
                if ff and ff[0] not in picked:
                    picked.append(ff[0])

    return picked[:3]


def run_chat_pipeline(
    question: str,
    lat=None, lng=None,
    special_location: Optional[str] = None,
    lang: str = "fr",
    intent_hint: Optional[str] = None,
    history: Optional[list] = None,
    is_session_active: bool = False,
) -> Dict:

    t0 = perf_counter()
    origin = _origin_from_inputs(lat, lng)

    # 0) Réponses simples (salut, merci, etc.)
    if _looks_like_chitchat(question):
        ans = write_final_answer(chitchat_prompt(question, lang)).strip()
        res = {
            "intent": Intent.AUTRE.value,
            "entities": {},
            "origin": {"lat": origin[0], "lng": origin[1]},
            "assistant_message": ans or ("Salut ! Comment puis-je t’aider à Tanger ?"),
        }
        res["_latency_s"] = round(perf_counter() - t0, 2)
        return res

    # 1) Intent + entités
    intent_pack = _classify(question, lang, intent_hint)
    intent = intent_pack.get("intent", Intent.AUTRE.value)
    entities = intent_pack.get("entities", {}) or {}

    # 2) Recentrage localisation (special_location > place_hint)
    if special_location:
        geo = geocode_text(special_location)
        if geo: origin = geo
    elif entities.get("place_hint"):
        hint = _normalize_hint(entities["place_hint"])
        geo = geocode_text(f"{hint}, {CITY_HINT}") or geocode_text(hint)
        if geo: origin = geo

    # 3) Candidats
    pois: List[Dict] = []
    if intent in (Intent.RESTAURANT.value, Intent.MONUMENT.value, Intent.HORAIRE.value):
        pois = fetch_candidates(intent, origin, special_location, entities)

    if intent == Intent.HORAIRE.value and entities.get("opening_subject"):
        ts = text_search(entities["opening_subject"], origin[0], origin[1])
        pois = normalize_poi_list(ts, origin[0], origin[1]) + pois
        pois = enrich_with_details(pois[:20])

    poi_cards = _build_poi_cards(pois) if pois else ""

    # 4) Branches d'intent
    if intent == Intent.TRADUCTION.value:
        ans = write_final_answer(translation_prompt(question)).strip() or "Voici la traduction."
        res = {"intent": intent, "entities": entities, "origin": {"lat": origin[0], "lng": origin[1]}, "assistant_message": ans}

    elif intent == Intent.ITINERAIRE.value:
        if not pois:
            msg = write_final_answer(no_poi_fallback_prompt(question, lang, _location_note(origin, special_location))).strip()
            res = {"intent": intent, "entities": entities, "origin": {"lat": origin[0], "lng": origin[1]},
                   "selection": [], "route": None, "llm_reasoning": {}, "assistant_message": msg, "candidates_considered": 0}
        else:
            rank_prompt = ranking_and_answer_prompt(
                question, lang, intent, json.dumps(entities, ensure_ascii=False),
                poi_cards or "- aucun résultat prox.", _location_note(origin, special_location)
            )
            raw = write_json_answer(rank_prompt, _schema_hint_for_intent(intent))
            rank_json = _parse_llm_json(raw, no_raise=True) or {}
            sel_ids = (rank_json.get("selected_place_ids") or [])[:3]
            sel_names = (rank_json.get("selected_place_names") or [])[:3]
            picked = _match_selection(sel_ids, sel_names, pois, origin)

            # fallback si le LLM ne choisit rien
            if not picked:
                picked = pois[:1]

            route_info = None
            if picked and bool(rank_json.get("route_needed", True)):
                route_info = route_for_selection(origin, picked[0], mode="walking")

            msg = rank_json.get("explanation") or (f"Je te propose {', '.join(p['name'] for p in picked[:2])}." if picked else
                                                   write_final_answer(no_poi_fallback_prompt(question, lang, _location_note(origin, special_location))).strip())
            res = {"intent": intent, "entities": entities, "origin": {"lat": origin[0], "lng": origin[1]},
                   "selection": [p.get("place_id") for p in picked[:1]], "route": route_info,
                   "llm_reasoning": rank_json, "assistant_message": msg, "candidates_considered": len(pois)}

    elif intent == Intent.HORAIRE.value:
        if not pois:
            msg = write_final_answer(no_poi_fallback_prompt(question, lang, _location_note(origin, special_location))).strip()
            res = {"intent": intent, "entities": entities, "origin": {"lat": origin[0], "lng": origin[1]},
                   "opening_answer": {}, "assistant_message": msg, "candidates_considered": 0}
        else:
            oh_prompt = opening_hours_answer_prompt(question, lang, poi_cards or "-")
            raw = write_json_answer(oh_prompt, _schema_hint_for_intent(intent))
            oh_json = _parse_llm_json(raw, no_raise=True) or {}
            msg = oh_json.get("explanation") or "Voici ce que j’ai trouvé pour les horaires à proximité."
            res = {"intent": intent, "entities": entities, "origin": {"lat": origin[0], "lng": origin[1]},
                   "opening_answer": oh_json, "assistant_message": msg, "candidates_considered": len(pois)}

    elif intent in (Intent.RESTAURANT.value, Intent.MONUMENT.value):
        if not pois:
            msg = write_final_answer(no_poi_fallback_prompt(question, lang, _location_note(origin, special_location))).strip()
            res = {"intent": intent, "entities": entities, "origin": {"lat": origin[0], "lng": origin[1]},
                   "candidates_considered": 0, "selections": [], "llm_reasoning": {}, "route": None,
                   "assistant_message": msg}
        else:
            rank_prompt = ranking_and_answer_prompt(
                question, lang, intent, json.dumps(entities, ensure_ascii=False),
                poi_cards or "-", _location_note(origin, special_location)
            )
            raw = write_json_answer(rank_prompt, _schema_hint_for_intent(intent))
            rank_json = _parse_llm_json(raw, no_raise=True) or {}
            sel_ids = (rank_json.get("selected_place_ids") or [])[:3]
            sel_names = (rank_json.get("selected_place_names") or [])[:3]
            picked = _match_selection(sel_ids, sel_names, pois, origin)

            # fallback si le LLM ne choisit rien
            if not picked:
                picked = pois[:3]

            selections = [{
                "place_id": p["place_id"],
                "name": p["name"],
                "lat": p["lat"],            # ⬅️ ajoute
                "lng": p["lng"],            # ⬅️ ajoute
                "distance_m": p["distance_m"],
                "rating": p.get("rating"),
                "user_ratings_total": p.get("user_ratings_total"),
                "price_level": p.get("price_level"),
                "maps_url": p.get("maps_url"),
                "opening_hours": p.get("opening_hours")
            } for p in picked]


            route_info = None
            if picked and rank_json.get("route_needed"):
                route_info = route_for_selection(origin, picked[0], mode="walking")

            msg = rank_json.get("explanation") or (
                f"Voici {', '.join(p['name'] for p in picked)} près de toi."
            )
            res = {"intent": intent, "entities": entities, "origin": {"lat": origin[0], "lng": origin[1]},
                   "candidates_considered": len(pois), "selections": selections,
                   "llm_reasoning": rank_json, "route": route_info, "assistant_message": msg}

    else:
        ans = write_final_answer(chitchat_prompt(question, lang)).strip()
        res = {"intent": intent, "entities": entities, "origin": {"lat": origin[0], "lng": origin[1]},
               "assistant_message": ans or "Salut ! Comment puis-je t'aider à explorer la ville ?"}

    res["_latency_s"] = round(perf_counter() - t0, 2)
    return res
