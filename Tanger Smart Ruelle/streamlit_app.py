import time, requests, pandas as pd, pydeck as pdk, streamlit as st

def api_get(base, path, timeout=10):
    url = f"{base.rstrip('/')}{path}"
    return requests.get(url, timeout=timeout)

def api_post(base, path, payload, timeout=60):
    url = f"{base.rstrip('/')}{path}"
    return requests.post(url, json=payload, timeout=timeout)

st.set_page_config(page_title="Tanger Smart Ruelle – Test", page_icon="🧭", layout="wide")
st.title("🧭 Tanger Smart Ruelle — Client de test Streamlit")

with st.sidebar:
    st.header("⚙️ Paramètres")
    api_base = st.text_input("URL de l'API", value="http://localhost:5000")
    health = "❓"
    try:
        r = api_get(api_base, "/healthz", timeout=3)
        health = "🟢 OK" if r.status_code == 200 else f"🟠 {r.status_code}"
    except Exception as e:
        health = f"🔴 {e.__class__.__name__}"
    st.write(f"**Santé API** : {health}")

    if "session_id" not in st.session_state:
        st.session_state.session_id = None
    if st.button("🆕 Démarrer une session"):
        try:
            rr = api_post(api_base, "/start_session", {})
            st.session_state.session_id = rr.json().get("session_id")
            st.success(f"Session démarrée: {st.session_state.session_id}")
        except Exception as e:
            st.error(f"Impossible de démarrer la session: {e}")

    st.text_input("Session ID (lecture seule)", value=st.session_state.session_id or "", disabled=True)

    st.divider()
    st.caption("📍 Localisation (optionnelle)")
    colA, colB = st.columns(2)
    with colA: lat = st.text_input("Latitude", value="")
    with colB: lng = st.text_input("Longitude", value="")
    special_location = st.text_input("Localisation spéciale (ex: 'Kasbah Tanger')", value="")

    st.caption("🌐 Langue & intention (facultatif)")
    lang = st.selectbox("Langue", ["fr", "ar", "en"], index=0)
    intent_hint = st.selectbox(
        "Forcer l'intention (optionnel)",
        ["", "restaurant", "monument", "itineraire", "horaire", "traduction", "autre", "negatif"],
        index=0
    )

    st.divider()
    st.caption("🧪 Exemples (clique pour remplir)")
    ex_cols = st.columns(3)
    if ex_cols[0].button("🥘 Couscous pas cher"):
        st.session_state["question_input"] = "Où manger un couscous pas cher ?"
    if ex_cols[1].button("☕ Café à la Marina"):
        st.session_state["question_input"] = "Je veux un café sympa à la Marina"
    if ex_cols[2].button("🏛️ Monuments Kasbah"):
        st.session_state["question_input"] = "Que visiter comme monument autour de la Kasbah ?"

# Zone de saisie
question = st.text_area("💬 Ta question", key="question_input", height=100, placeholder="Ex: Où manger un poisson grillé pas cher ?")

col_run = st.columns([1,1,6])
with col_run[0]:
    send_btn = st.button("🚀 Envoyer", type="primary", use_container_width=True)
with col_run[1]:
    clear_btn = st.button("🧹 Vider", use_container_width=True)

if clear_btn:
    for k in ("last_response", "question_input"):
        st.session_state.pop(k, None)
    st.experimental_rerun()

if send_btn:
    if not st.session_state.session_id:
        st.error("Commence par démarrer une session dans la sidebar.")
    elif not question.strip():
        st.warning("Entre une question 😉")
    else:
        payload = {"session_id": st.session_state.session_id, "question": question.strip(), "lang": lang}
        try:
            if lat and lng:
                payload["lat"] = float(lat); payload["lng"] = float(lng)
        except Exception:
            st.warning("Latitude/Longitude invalides — ignorées.")
        if special_location.strip(): payload["special_location"] = special_location.strip()
        if intent_hint.strip(): payload["intent_hint"] = intent_hint.strip()

        with st.spinner("Je réfléchis…"):
            t0 = time.time()
            try:
                resp = api_post(api_base, "/chat", payload, timeout=60)
                dt = time.time() - t0
                if resp.status_code != 200:
                    st.error(f"Erreur API: {resp.status_code} – {resp.text}")
                else:
                    js = resp.json(); js["_latency_s"] = round(dt, 2)
                    st.session_state.last_response = js
            except Exception as e:
                st.error(f"Erreur de connexion à l'API: {e}")

resp = st.session_state.get("last_response")
if resp:
    st.subheader("🧠 Résultat")
    c1, c2, c3, c4 = st.columns(4)
    c1.metric("Intention", resp.get("intent", "—"))
    ent = resp.get("entities") or {}
    c2.metric("Entités détectées", ", ".join([f"{k}:{v}" for k, v in ent.items() if v]) or "—")
    origin = resp.get("origin") or {}
    c3.metric("Origine", f"{origin.get('lat','?')}, {origin.get('lng','?')}")
    c4.metric("Latence (s)", resp.get("_latency_s", "—"))

    msg = resp.get("assistant_message")
    if msg: st.success(msg)

    selections = resp.get("selections") or []
    if selections:
        st.markdown("### ⭐ Sélections proposées")
        df = pd.DataFrame([{
            "Nom": s.get("name"),
            "Distance (m)": s.get("distance_m"),
            "Note": s.get("rating"),
            "Avis": s.get("user_ratings_total"),
            "Prix": s.get("price_level"),
            "URL": s.get("maps_url"),
            "Ouvertures": "; ".join((s.get("opening_hours") or {}).get("weekday_text") or []) if s.get("opening_hours") else ""
        } for s in selections])
        st.dataframe(df, use_container_width=True, hide_index=True)

        try:
            points = []
            if origin:
                points.append({"name": "Vous (origine)", "lat": float(origin.get("lat")), "lng": float(origin.get("lng")), "color": [0,180,0,200], "size": 12})
            for s in selections:
                if s.get("lat") and s.get("lng"):
                    points.append({"name": s.get("name","POI"), "lat": float(s["lat"]), "lng": float(s["lng"]), "color": [200,30,30,220], "size": 10})
            view_state = pdk.ViewState(latitude=float(origin.get("lat", 35.7847)), longitude=float(origin.get("lng", -5.8129)), zoom=14, pitch=0)
            layers = []
            if points:
                layers.append(pdk.Layer("ScatterplotLayer", data=points, get_position="[lng, lat]", get_fill_color="color", get_radius="size", pickable=True))
            st.pydeck_chart(pdk.Deck(map_style="road", initial_view_state=view_state, layers=layers))
        except Exception:
            st.info("ℹ️ Pour voir les points POI sur la carte, assure-toi que l'API renvoie lat/lng (c’est fait).")

    if resp.get("route"):
        st.markdown("### 🗺️ Itinéraire")
        url = resp["route"].get("route_url")
        if url: st.markdown(f"[Ouvrir l’itinéraire]({url})")

    if resp.get("opening_answer"):
        st.markdown("### ⏰ Horaires")
        oh = resp["opening_answer"]
        st.write(oh.get("explanation") or "—")

    with st.expander("🧩 JSON complet (debug)"):
        st.json(resp)

st.divider()
st.caption("© Tanger Smart Ruelle — Demo Streamlit. Backend: Flask/Waitress + LLaMA AWQ + OSM/Google.")
