from intents import INTENT_LABELS
from config import CITY_HINT

INTENTS_LIST = ", ".join(f'"{x}"' for x in INTENT_LABELS)

def classify_and_extract_prompt(question: str, lang: str = "fr"):
    return f"""
Tu es un assistant touristique multilingue (FR/AR/EN) pour {CITY_HINT}.
Analyse la question de l'utilisateur et retourne un JSON compact et valide.

Champs à retourner:
- "intent": une des valeurs [{INTENTS_LIST}]
- "entities": {{
    "cuisine": (ex: "poisson", "couscous", "grillades", null si non précisé),
    "budget":  (ex: "pas cher", "moyen", "haut", null),
    "indoor_outdoor": ("interieur" | "terrasse" | null),
    "time_ref": (ex: "aujourd'hui 19h", "vendredi midi", null),
    "place_hint": (indice de lieu/quartier, ex: "Kasbah", "Marina", null),
    "route_target": (destination si itinéraire, ex: "musée de la Kasbah", null),
    "opening_subject": (si l'utilisateur demande l'horaire d'un lieu précis, son nom ou null)
  }},
- "needs_location": true/false

Rappels rapides:
- Restaurants -> "restaurant".
- Monuments/visite -> "monument".
- "Comment aller / itinéraire / route" -> "itineraire".
- "A quelle heure ouvre/ferme X" -> "horaire".
- "Comment dire ... en arabe" -> "traduction".
- Sinon -> "autre".

Question ({lang}):
{question}
""".strip()

def ranking_and_answer_prompt(question: str, lang: str, intent: str, entities_json: str, poi_cards: str, location_note: str):
    return f"""
Tu es un guide local expert à {CITY_HINT}.

RÈGLES IMPORTANTES:
- N'invente AUCUN lieu. Utilise UNIQUEMENT les POI listés dans "Cartes-POI".
- Les IDs retournés doivent venir de "id=..." dans les cartes.
- Donne aussi "selected_place_names" EXACTEMENT comme dans les cartes.
- Si aucune carte n'est fournie ou si rien ne correspond, renvoie des listes vides et une explication honnête.
- Reste strictement dans la zone de {CITY_HINT}.

Format JSON strict:
{{
  "selected_place_ids": ["<place_id_from_cards>", "..."],       // <=3
  "selected_place_names": ["<exact_name_from_cards>", "..."],    // <=3
  "explanation": "<2-4 phrases lisibles en {lang}>",
  "route_needed": <true|false>,
  "follow_up_needed": <true|false>,
  "follow_up_question": "<si follow_up_needed, une question brève>"
}}

Cartes-POI (max 12):
{poi_cards}

Contexte de localisation:
{location_note}

Question utilisateur:
{question}

Intention & entités détectées:
{entities_json}
""".strip()

def opening_hours_answer_prompt(question: str, lang: str, poi_cards: str):
    return f"""
Tu es un guide local. L'utilisateur pose une question sur les horaires d'ouverture/fermeture.
Tu reçois des "cartes POI" candidates (avec horaires si disponibles).

Règles:
- N'invente rien. Utilise uniquement les POI des cartes.
- Si un lieu précis est reconnu, remplis best_match_* avec son id et son nom.
- Si les horaires sont incertains, mets "is_open_now": null et "today_hours": null.

Retourne un JSON STRICT:
{{
  "best_match_place_id": "<place_id_from_cards_or_null>",
  "best_match_place_name": "<name_from_cards_or_null>",
  "is_open_now": <true|false|null>,
  "today_hours": "<HH:MM–HH:MM ou null>",
  "explanation": "<réponse naturelle et concise en {lang}>"
}}

Cartes-POI:
{poi_cards}

Question:
{question}
""".strip()

def translation_prompt(question: str):
    return f"""
Tu es un petit lexique FR-AR-EN.
Si l'utilisateur demande "comment dire X en arabe", donne:
- arabe moderne standard (+ dialecte marocain si utile),
- transcription latine,
- 1-2 exemples d'usage.
Réponds en français, concis.
""".strip()

def classify_force_json_prompt(question: str, lang: str = "fr"):
    intents = ",".join([f'"{x}"' for x in INTENT_LABELS])
    return f"""Retourne CE JSON minifié en remplissant les valeurs déductibles, sinon laisse null:
{{"intent":"","entities":{{"cuisine":null,"budget":null,"indoor_outdoor":null,"time_ref":null,"place_hint":null,"route_target":null,"opening_subject":null}},"needs_location":true}}
Règles:
- "intent" ∈ [{intents}]
- AUCUN texte hors JSON.
Question ({lang}): {question}"""

# --- NO-POI fallback & chitchat ---
def no_poi_fallback_prompt(question: str, lang: str, location_note: str):
    return f"""
Tu es un guide local à {CITY_HINT}. Aucune donnée cartographique exploitable n'est disponible pour répondre précisément.
Donne une réponse utile et prudente en {lang} :
- Conseils généraux (quartiers/axes connus), comment s'orienter,
- Pas de recommandations nommées (NE CITE AUCUN NOM DE LIEU),
- Propose une question de suivi pour affiner (budget, quartier, à pied/voiture, etc.).
Ne mentionne pas d'autres villes.
Contexte: {location_note}
Question: {question}
""".strip()

def chitchat_prompt(question: str, lang: str):
    return f"""
Tu es un assistant amical et concis pour {CITY_HINT}.
Objectif: répondre aux salutations / messages sociaux SANS recommandations ni inventions.

RÈGLES:
- Si le message est une salutation, un remerciement ou un court small talk, réponds en 1 ou 2 phrases max.
- Ne répète pas la question de l’utilisateur.
- N’invente AUCUN lieu, AUCUNE adresse, AUCUNE recommandation.
- Termine si possible par une question brève pour proposer de l’aide (ex: “Tu cherches un resto, un monument, un itinéraire ou des horaires?”).
- Pas de “Réponse:”, pas de listes, pas de markdown lourd.
- Réponds uniquement en {lang}.

Utilisateur:
{question}

Consigne finale: réponds maintenant, en 1–2 phrases, en {lang}, sans proposer de lieux.
""".strip()
