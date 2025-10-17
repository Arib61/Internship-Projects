from flask import Flask, request, jsonify
from waitress import serve
from cachetools import LRUCache
from cachetools.keys import hashkey

from session_store import create_session, load_history, save_history
from pipeline import run_chat_pipeline

app = Flask(__name__)
_pipeline_cache = LRUCache(maxsize=300)

def cache_key_from_payload(payload: dict):
    question = (payload.get("question") or "").strip().lower()
    spec_loc  = (payload.get("special_location") or "").strip().lower()
    intent_hint = (payload.get("intent_hint") or "").strip().lower()
    lang = (payload.get("lang") or "fr").lower()
    lat = str(payload.get("lat") or "")
    lng = str(payload.get("lng") or "")
    return hashkey(question, spec_loc, intent_hint, lang, lat, lng)

@app.get("/healthz")
def healthz():
    return "OK", 200

@app.post("/start_session")
def start_session():
    sid = create_session()
    return jsonify({"session_id": sid}), 200

@app.post("/chat")
def chat():
    data = request.get_json(force=True, silent=True) or {}
    session_id = data.get("session_id")
    question   = (data.get("question") or "").strip()
    if not session_id or not question:
        return jsonify({"error": "Fournir 'session_id' et 'question'."}), 400

    history = load_history(session_id)
    is_active = bool(history)

    ckey = cache_key_from_payload(data)
    try:
        if ckey in _pipeline_cache:
            result = _pipeline_cache[ckey]
        else:
            result = run_chat_pipeline(
                question=question,
                lat=data.get("lat"), lng=data.get("lng"),
                special_location=data.get("special_location"),
                lang=(data.get("lang") or "fr"),
                intent_hint=data.get("intent_hint"),
                history=history, is_session_active=is_active,
            )
            _pipeline_cache[ckey] = result
    except Exception as e:
        result = {
            "intent": "autre", "entities": {}, "origin": {},
            "assistant_message": "Désolé, le service est saturé pour l’instant. Donne-moi un type de lieu et un quartier (ex: « café sympa à la Marina ») et je ferai au mieux.",
            "error": f"{type(e).__name__}: {e}", "_latency_s": 0
        }

    history.append({"question": question, "result": result})
    save_history(session_id, history)
    return jsonify(result), 200

if __name__ == "__main__":
    print("🚀 Tanger Smart Ruelle – API conversationnelle")
    serve(app, host="0.0.0.0", port=5000, threads=8)
