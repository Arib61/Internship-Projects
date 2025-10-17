import streamlit as st
import requests
import json
import pandas as pd
from questions_reduites_shuffled_copy import questions

# --- Configuration de l'API ---
FLASK_API_URL = "http://localhost:5000/generate_sql"
EXECUTE_SQL_URL = "http://localhost:5000/execute_sql"
START_SESSION_URL = "http://localhost:5000/start_session"
END_SESSION_URL = "http://localhost:5000/end_session"

# --- Initialisation Session State ---
if "session_id" not in st.session_state:
    st.session_state.session_id = None
if "history" not in st.session_state:
    st.session_state.history = []
if "last_sql_generated" not in st.session_state:
    st.session_state.last_sql_generated = None

# --- Configuration de la page ---
st.set_page_config(page_title="NLP to SQL", layout="centered", page_icon="🔍")
st.title("🔍 NLP → SQL Pipeline")
st.markdown("Interrogez votre base de données avec du langage naturel")
tab1, tab2, tab3 = st.tabs(["Question Libre", "Test Multi-Questions", "Batch Session Unique"])

# --- Sidebar: Gestion Session ---
with st.sidebar:
    st.header("📋 Gestion de Session")
    col1, col2 = st.columns(2)
    
    with col1:
        if st.button("🔄 Nouvelle Session", use_container_width=True):
            try:
                response = requests.post(START_SESSION_URL)
                if response.status_code == 200:
                    session_id = response.json()["session_id"]
                    st.session_state.session_id = session_id
                    st.session_state.history = []
                    st.success(f"Session démarrée !\nID: `{session_id}`")
                else:
                    st.error("Erreur création session")
            except Exception as e:
                st.error(f"Connexion API impossible: {e}")
    
    with col2:
        if st.button("❌ Terminer Session", use_container_width=True, disabled=not st.session_state.session_id):
            try:
                if st.session_state.session_id:
                    payload = {"session_id": st.session_state.session_id}
                    requests.post(END_SESSION_URL, json=payload)
            except Exception:
                pass
            finally:
                st.session_state.session_id = None
                st.session_state.history = []
                st.success("Session terminée")
    
    st.divider()
    
    # --- Afficher l'état de la session ---
    st.subheader("État Actuel")
    if st.session_state.session_id:
        st.success(f"**Session active:**\n`{st.session_state.session_id}`")
        st.info(f"{len(st.session_state.history)} requêtes dans l'historique")
    else:
        st.warning("Aucune session active")
    
    st.divider()
    
    # --- Vérification API ---
    st.subheader("🧪 Vérification API")
    if st.button("Tester la connexion API", use_container_width=True):
        try:
            health_url = FLASK_API_URL.replace("/generate_sql", "/health")
            response = requests.get(health_url, timeout=5)
            if response.status_code == 200:
                st.success("✅ API connectée et opérationnelle")
            else:
                st.error(f"❌ Statut API: {response.status_code}")
        except Exception as e:
            st.error(f"🔌 Connexion impossible: {e}")

# --- Tab 1: Question Libre ---
with tab1:
    st.subheader("🧠 Posez votre question")
    user_question = st.text_area(
        "Formulez votre question en langage naturel:",
        value="Quels types d'opérations ont été réalisées en 2025 ?",
        height=120,
        placeholder="Ex: Combien de patients ont été admis en urgence ce mois-ci?"
    )
    
    # --- Section Etat Session ---
    session_status = st.container()
    if not st.session_state.session_id:
        session_status.warning("⚠️ Démarrez une session dans la barre latérale")
    else:
        session_status.success(f"Session active: `{st.session_state.session_id}`")
    
    # --- Bouton d'exécution ---
    if st.button("Générer la requête SQL", type="primary", use_container_width=True, key="gen1"):
        if not user_question.strip():
            st.warning("Veuillez saisir une question")
        elif not st.session_state.session_id:
            st.warning("Démarrez d'abord une session")
        else:
            with st.status("**Traitement en cours...**", expanded=True) as status:
                try:
                    # Envoi à l'API
                    status.write("⚡ Envoi à l'API Flask...")
                    payload = {"question": user_question, "session_id": st.session_state.session_id}
                    response = requests.post(FLASK_API_URL, json=payload)

                    if response.status_code == 200:
                        result = response.json()
                        status.update(label="✅ Traitement réussi!", state="complete")
                        
                        # Stockage historique
                        st.session_state.history.append({
                            "question": user_question,
                            "result": result
                        })
                        
                        # Affichage principal
                        st.subheader("📝 Requête SQL Générée")
                        final_sql = result.get("sql_validated_post_exec", "")
                        st.session_state.last_sql_generated = final_sql
                        st.code(final_sql, language="sql")
                        
                        # Réponse naturelle
                        st.subheader("💬 Réponse Naturelle")
                        natural_response = result.get("user_friendly_answer", "")
                        if natural_response:
                            st.success(natural_response)
                        else:
                            st.info("Aucune réponse générée")

                        
                        st.subheader("💬Raison Llama")
                        raison_response = result.get("llama_reason", "")
                        if natural_response:
                            st.success(raison_response)
                        else:
                            st.info("Aucune raison générée")
                        
                        # Résultats d'exécution
                        st.subheader("📊 Résultats d'Exécution")
                        final_result = result.get("final_result")
                        if final_result and final_result.get("success"):
                            rows = final_result.get("rows", [])
                            columns = final_result.get("columns", [])
                            if rows:
                                df = pd.DataFrame(rows, columns=columns)
                                st.dataframe(df, use_container_width=True)
                            else:
                                st.info("Aucun résultat retourné (0 lignes)")
                        elif final_result:
                            st.error(f"Erreur d'exécution: {final_result.get('error', 'Inconnue')}")
                        
                        # Détails du pipeline
                        with st.expander("🔍 Détails du Pipeline"):
                            tabs = st.tabs(["Étapes SQL", "Logs Techniques"])
                            
                            with tabs[0]:
                                st.subheader("1. Génération Initiale (SQLCoder-7b)")
                                st.code(result.get("sql_code_initial_a", ""), language="sql")
                                
                                st.subheader("2. Renforcement (SQLCoder-8b)")
                                st.code(result.get("sql_code_initial_b", ""), language="sql")
                                
                                st.subheader("3. Validation (Llama)")
                                st.code(result.get("sql_validated_llama", ""), language="sql")
                            
                            with tabs[1]:
                                st.json(result.get("log", {}))
                            
                        # Métriques
                        st.caption(f"⏱️ Durée totale: {result.get('duration_total_pipeline', 0):.2f}s | " +
                                  f"Schéma utilisé: {result.get('schema_rag', 'N/A')}")
                    else:
                        st.error(f"Erreur API: {response.status_code}")
                        st.json(response.json())
                except Exception as e:
                    status.error(f"Erreur critique: {str(e)}")

# --- Tab 2: Test Multi-Questions ---
with tab2:
    st.subheader("🧪 Test Automatisé Multi-Questions")
    st.info("Exécute séquentiellement toutes les questions de test avec une nouvelle session par question")
    
    if st.button("Lancer le Test Complet", type="primary", use_container_width=True, key="batch_full"):
        results = []
        progress_bar = st.progress(0, text="Initialisation...")
        status_text = st.empty()
        
        for i, question in enumerate(questions):
            try:
                # Mise à jour UI
                progress = int((i + 1) / len(questions) * 100)
                progress_bar.progress(progress, text=f"Traitement ({i+1}/{len(questions)})")
                status_text.info(f"**Question en cours:**\n{question}")
                
                # Création session
                session_res = requests.post(START_SESSION_URL)
                session_id = session_res.json().get("session_id") if session_res.ok else None
                
                if not session_id:
                    results.append({"question": question, "error": "Erreur création session"})
                    continue
                
                # Appel API
                payload = {"question": question, "session_id": session_id}
                response = requests.post(FLASK_API_URL, json=payload)
                
                if response.ok:
                    result = response.json()
                    final_sql = result.get("sql_validated_post_exec", "")
                    natural_response = result.get("user_friendly_answer", "")
                    exec_result = result.get("final_result", {})
                    
                    # Formatage résultat
                    result_entry = {
                        "Question": question,
                        "SQL Généré": final_sql,
                        "Réponse Naturelle": natural_response,
                        "Statut Exécution": "Succès" if exec_result.get("success") else "Échec",
                        "Erreur": exec_result.get("error", ""),
                        "Lignes Retournées": len(exec_result.get("rows", [])),
                        "Durée (s)": result.get("duration_total_pipeline", 0)
                    }
                    results.append(result_entry)
                else:
                    results.append({"question": question, "error": f"Erreur API {response.status_code}"})
                
                # Nettoyage session
                requests.post(END_SESSION_URL, json={"session_id": session_id})
                
            except Exception as e:
                results.append({"question": question, "error": str(e)})
        
        # Affichage résultats
        progress_bar.empty()
        status_text.empty()
        
        if results:
            df_results = pd.DataFrame(results)
            st.success("✅ Traitement terminé!")
            
            # Affichage tableau
            st.subheader("📊 Résumé des Résultats")
            st.dataframe(df_results, use_container_width=True, hide_index=True)
            
            # Téléchargement
            csv = df_results.to_csv(index=False).encode('utf-8')
            st.download_button(
                label="💾 Télécharger les résultats (CSV)",
                data=csv,
                file_name="results_nlp_sql.csv",
                mime="text/csv"
            )


# --- Tab 3: Batch Session Indépendant ---
import streamlit as st
import pandas as pd
import requests

with tab3:
    st.subheader("⚡ Batch Sessions Indépendants")
    st.info("Chaque question est traitée dans une session séparée (isolation totale)")

    if st.button("Lancer le Batch (sessions indépendantes)", type="primary", use_container_width=True, key="batch_multi"):
        try:
            results = []
            progress_bar = st.progress(0, text="Initialisation...")
            excel_file = "results_complet_final.xlsx"

            for i, question in enumerate(questions):
                # --- 1) Créer une session pour cette question ---
                session_res = requests.post(START_SESSION_URL)
                session_id = session_res.json().get("session_id") if session_res.ok else None

                if not session_id:
                    results.append({
                        "Question": question,
                        "Statut": "❌",
                        "Erreur": "Échec création session"
                    })
                    continue

                # --- 2) Mise à jour UI ---
                progress = int((i + 1) / len(questions) * 100)
                progress_bar.progress(progress, text=f"Traitement ({i+1}/{len(questions)})")

                # --- 3) Appel API ---
                payload = {"question": question, "session_id": session_id}
                response = requests.post(FLASK_API_URL, json=payload)

                if response.ok:
                    result = response.json()
                    final_sql = result.get("sql_validated_post_exec", "")
                    natural_response = result.get("user_friendly_answer", "")
                    exec_result = result.get("final_result", {})
                    raison = result.get("llama_reason", "")

                    results.append({
                        "Question": question,
                        "SQL Final": final_sql,
                        "Réponse": natural_response,
                        "Statut": "✅" if exec_result.get("success") else "❌",
                        "Lignes": len(exec_result.get("rows", [])),
                        "Raison": raison,
                        "Durée (s)": f"{result.get('duration_total_pipeline', 0):.2f}"
                    })
                else:
                    results.append({
                        "Question": question,
                        "Statut": "❌",
                        "Erreur": f"API {response.status_code}"
                    })

                # --- 4) Sauvegarde Excel après chaque question ---
                df_results = pd.DataFrame(results)
                df_results.to_excel(excel_file, index=False)

                # --- 5) Fermer la session (cleanup) ---
                requests.post(END_SESSION_URL, json={"session_id": session_id})

            # Nettoyage final UI
            progress_bar.empty()

            # Bouton de téléchargement unique
            with open(excel_file, "rb") as f:
                st.download_button(
                    label="📥 Télécharger Excel (à jour)",
                    data=f,
                    file_name=excel_file,
                    mime="application/vnd.ms-excel",
                    key="download_excel_live"
                )

            # Résultats finaux
            st.success(f"✅ Batch terminé - {len(questions)} questions traitées (sessions indépendantes)")
            st.dataframe(df_results, use_container_width=True, hide_index=True)

        except Exception as e:
            st.error(f"Erreur critique: {str(e)}")
