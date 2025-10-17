def build_llama_combined_reason_and_answer_prompt2(question, sql, columns, rows, max_lines=20):
    import pandas as pd

    # Aperçu du résultat pour donner du contexte
    if not (columns and rows):
        df_str = "[Aucun résultat]"
        shape_hint = "VIDE"
    else:
        df = pd.DataFrame(rows, columns=columns)
        df_str = df.head(max_lines).to_string(index=False)
        if (len(df) == 1 and len(df.columns) == 1):
            shape_hint = "SINGLE_VALUE"
        elif (len(df.columns) == 1 and len(df) > 1):
            shape_hint = "ONE_COL_MULTI_ROWS"
        else:
            shape_hint = "TABLE"

    # Directives communes: exactement deux lignes, et FR naturel
    base_directives = [
        "Tu dois répondre en EXACTEMENT DEUX lignes et rien d'autre:",
        "Raison: <1 phrase expliquant ce que veut l'utilisateur et comment la requête le satisfait>",
        "Réponse naturelle: <phrase courte en français, claire et naturelle; pas de code, pas de balises, pas de SQL>",
        "Règle absolue: renvoie TOUJOURS une reformulation en langage naturel. Ne renvoie JAMAIS une valeur brute seule.",
        "Termine ta sortie par le marqueur [END]."
    ]

    # Directives spécifiques selon la forme du résultat
    if shape_hint == "VIDE":
        extra = [
            "Si le résultat est vide, écris en 'Réponse naturelle': 'Aucun résultat.' (ou équivalent bref)."
        ]
    elif shape_hint == "SINGLE_VALUE":
        extra = [
            "Si une seule valeur est renvoyée, la 'Réponse naturelle' doit être UNE PHRASE nature; cite la valeur et son sens (d'après la question/nom de colonne) de façon concise."
        ]
    elif shape_hint == "ONE_COL_MULTI_ROWS":
        extra = [
            "Si une seule colonne avec plusieurs lignes est renvoyée, la 'Réponse naturelle' doit être une phrase brève introduisant une liste concise des valeurs distinctes, séparées par des virgules."
        ]
    else:  # TABLE
        extra = [
            "Si plusieurs colonnes/lignes sont renvoyées, la 'Réponse naturelle' doit résumer brièvement ce que montre le tableau; si pertinent, mentionne 2 à 5 éléments clés (ex: totaux, catégories principales) dans la même phrase."
        ]

    directives = "\n- ".join(["- " + d for d in (base_directives + extra)])

    return f""" {directives}

Contexte:
Question: {question}
SQL: {sql}
Résultat:
{df_str}

Commence tout de suite en écrivant la ligne 'Raison:' puis 'Réponse naturelle:'.
Raison: """
