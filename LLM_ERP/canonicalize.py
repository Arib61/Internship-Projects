import re

# 1) – Mots-clés métier
KEYWORDS = [
    r"unité opérationnelle", r"poste comptable", r"statut de validation",
    r"code ligne", r"ligne budgétaire", r"codification", r"ministère",
    r"mission", r"programme", r"titre budgétaire", r"titre", r"uo", r"pc", r"type",
    r"mode de paiement"
]

# 2) – Regex utilitaires
KW_RE = re.compile(rf"\b({'|'.join(KEYWORDS)})\b", re.I)
DATE_RE = re.compile(r"\b(19|20)\d{2}-\d{2}-\d{2}(?: \d{2}:\d{2}(?::\d{2})?)?\b")
YEAR_ONLY_RE = re.compile(r"\b(19|20)\d{2}\b(?!-\d{2}-\d{2})")
TIME_CUE_RE = re.compile(r"\bentre\b|\ben\b\s+(?:année\s+)?(19|20)\d{2}\b", re.I)
LIAISON_RE = re.compile(r"\b(et|ou|pour|avec)\b", re.I)
PUNCT_RE = re.compile(r"[.?!;:](?=\s|$)")
MULTI_WS = re.compile(r"\s+")
FINAL_DET_RE = re.compile(r"\s+(l[eaes]|d[eu]|des|un|une)\s*$", re.I)
SMALL_VERB_RE = re.compile(r"\b(ont|est|sont|sera|seront|a|avaient|avait|va)\b", re.I)

# 3) – Cherche la fin de la valeur pour un mot-clé
DET_RE = re.compile(
    r"""
        \s*                      # espaces
        (?:l[ea]|les|le|la|l'|   # le, la, l’, les…
           du|de\s+la|des|d[eu]| # du, de la, des, de, d’
           un|une)               # un, une
        \b\s*                    # mot entier + espaces
    """,
    re.I | re.VERBOSE
)

def _find_val_end(txt: str, start: int) -> int:
    boundaries = []
    pos = start

    # 1️⃣ prochain mot-clé
    m_kw = KW_RE.search(txt, pos)
    if m_kw:
        boundaries.append(m_kw.start())

    # 2️⃣ ponctuation forte
    m_punct = PUNCT_RE.search(txt, pos)
    if m_punct:
        boundaries.append(m_punct.start())

    DANS_ANNEE_RE = re.compile(r"\bdans l['’]ann(?:ée|ee)\b", re.I)
    m_dans_annee = DANS_ANNEE_RE.search(txt, pos)
    if m_dans_annee:
        boundaries.append(m_dans_annee.start())

    # 3️⃣ séparateurs temporels
    for rgx in (TIME_CUE_RE, DATE_RE, YEAR_ONLY_RE):
        m = rgx.search(txt, pos)
        if m:
            boundaries.append(m.start())

    # 4️⃣ liaison ouvrant un nouveau contexte
    for m_liaison in LIAISON_RE.finditer(txt, pos):
        liaison_start = m_liaison.start()
        after_txt = txt[m_liaison.end():]          # texte qui suit « et/ou/… »
        after_txt = after_txt.lstrip()
        # ⏩ on saute 0 ou 1 déterminant éventuel
        m_det = DET_RE.match(after_txt)
        if m_det:
            after_txt = after_txt[m_det.end():]

        if (KW_RE.match(after_txt) or DATE_RE.match(after_txt) or
            YEAR_ONLY_RE.match(after_txt) or SMALL_VERB_RE.match(after_txt)):
            boundaries.append(liaison_start)       # coupe *avant* « et »
            break

    return min(boundaries) if boundaries else len(txt)


# 4) – Fonction principale révisée
def canonicalize_question(q: str) -> str:
    q = MULTI_WS.sub(" ", q.strip())
    if not KW_RE.search(q):
        return q

    # Liste pour reconstruire la question
    parts = []
    last_end = 0

    # Trouver tous les mots-clés et leurs positions
    matches = list(KW_RE.finditer(q))
    
    for i, match in enumerate(matches):
        kw_start = match.start()
        kw_end = match.end()
        kw_text = match.group(0)
        
        # Ajouter le texte avant le mot-clé
        parts.append(q[last_end:kw_start])
        
        # Ajouter le mot-clé
        parts.append(kw_text)
        
        # Déterminer le début de la valeur
        val_start = kw_end
        
        # Déterminer la fin de la valeur
        if i < len(matches) - 1:
            # Si le prochain token est un mot-clé, la valeur s'arrête avant lui
            next_kw_start = matches[i+1].start()
            val_end = _find_val_end(q, val_start)
            val_end = min(val_end, next_kw_start)
        else:
            val_end = _find_val_end(q, val_start)
        
        # Extraire la valeur brute
        raw_val = q[val_start:val_end].strip()
        
        # Traiter la valeur
        if raw_val:
            # Supprimer les déterminants finaux inutiles
            clean_val = FINAL_DET_RE.sub("", raw_val).strip()
            
            # Gestion spéciale pour les valeurs numériques et dates
            if DATE_RE.fullmatch(clean_val) or clean_val.replace('.', '', 1).isdigit():
                parts.append(f" {clean_val} ")
            else:
                # Gérer les abréviations avec points
                clean_val = re.sub(r'\s*\.\s*', '.', clean_val)
                
                # Ajouter des guillemets seulement si nécessaire
                if not clean_val.startswith('"') or not clean_val.endswith('"'):
                    clean_val = f'"{clean_val}"'
                parts.append(f" {clean_val} ")
        
        # Mettre à jour la dernière position
        last_end = val_end
    
    # Ajouter le reste du texte après le dernier mot-clé
    parts.append(q[last_end:])
    
    # Reconstruire la question
    result = "".join(parts)
    result = MULTI_WS.sub(" ", result).strip()
    
    # Gestion des références temporelles
    result = re.sub(
        r"\b(en)\s+((19|20)\d{2})\b", 
        lambda m: f"{m.group(1)} année {m.group(2)}", 
        result, 
        flags=re.I
    )
    
    # Ajouter "année" pour les années isolées
    result = re.sub(
        r"(?<!\bannée\s)(?<!\ben\sannée\s)\b((19|20)\d{2})\b(?!-\d{2}-\d{2})",
        r"année \1",
        result
    )
    
    # Correction finale
    result = MULTI_WS.sub(" ", result).strip()
    return result[0].upper() + result[1:] if result else result


import re

_LETTERS = r"A-Za-zÀ-ÖØ-öø-ÿ"

# Match col = '...' ou col = LOWER('...')
_RX_EQ_STR = re.compile(
    rf"""
        (?P<col>                      # colonne (avec alias ou fonction éventuel)
            (?:LOWER\s*\([\w$#\.]+\)|[\w$#\.]+)
        )
        \s*=\s*
        (?P<quote>
            (?:LOWER\s*\('(?:''|[^'])*'\) | '(?:''|[^'])*')
        )
    """,
    re.VERBOSE | re.IGNORECASE,
)

# Match col [NOT] LIKE '...'
_RX_LIKE_STR = re.compile(
    rf"""
        (?P<col>LOWER\s*\([\w$#\.]+\)|[\w$#\.]+)
        \s+(NOT\s+)?LIKE\s+
        (?P<quote>'(?:''|[^'])*')
    """,
    re.VERBOSE | re.IGNORECASE,
)

def _escape_for_oracle(val: str) -> str:
    return re.sub(r"(?<!')'(?!')", "''", val)

def _sanitize_quote_value(value: str) -> str:
    value = value.replace('“', '').replace('”', '')
    value = value.replace('"', '')
    return value.strip()

def _like_pattern_from_value(value: str) -> str:
    # Remplace chaque espace par %, mais garde la ponctuation comme la virgule
    words = value.split()
    pattern = "%" + "%".join(words) + "%"
    return pattern

def add_like_lower(sql: str) -> str:
    def _repl_eq(m: re.Match) -> str:
        col = m.group("col")
        quote = m.group("quote")

        # Si la quote est de la forme LOWER('...'), on extrait la valeur interne
        if quote.strip().upper().startswith("LOWER("):
            value = quote.strip()[6:-1].strip("'")
        else:
            value = quote.strip()[1:-1]

        value = _sanitize_quote_value(value)

        # Pas de lettres (ex : '2025') → on ne change rien
        if not re.search(rf"[{_LETTERS}]", value):
            return m.group(0)

        value = _escape_for_oracle(value)
        value = _like_pattern_from_value(value)

        if not re.match(r"LOWER\s*\(", col, re.IGNORECASE):
            col_final = f"LOWER({col.strip()})"
        else:
            col_final = col.strip()

        return f"{col_final} LIKE LOWER('{value}')"

    def _repl_like(m: re.Match) -> str:
        col = m.group("col")
        not_kw = m.group(2) or ""
        quote = m.group("quote")
        value = quote[1:-1]
        value = _sanitize_quote_value(value)
        # On ne traite que si valeur contient des lettres
        if not re.search(rf"[{_LETTERS}]", value):
            return m.group(0)

        value = _escape_for_oracle(value)
        value = _like_pattern_from_value(value)
        col_final = col if re.match(r"LOWER\s*\(", col, re.IGNORECASE) else f"LOWER({col.strip()})"
        return f"{col_final} {not_kw}LIKE LOWER('{value}')"

    sql = _RX_EQ_STR.sub(_repl_eq, sql)
    sql = _RX_LIKE_STR.sub(_repl_like, sql)
    return sql



import re

def patch_numjc_date_extraction(sql: str) -> str:
    # Patch EXTRACT(YEAR FROM NUMJC) = 2025 → ANNEE = '2025'
    sql = re.sub(
        r"EXTRACT\s*\(\s*YEAR\s+FROM\s+\w*\.?NUMJC\s*\)\s*=\s*(\d{4})",
        r"ANNEE = '\1'",
        sql,
        flags=re.IGNORECASE
    )

    # Patch EXTRACT(MONTH FROM CAST(NUMJC AS DATE)) → SUBSTR(NUMJC, 6, 2)
    if re.search(r"EXTRACT\s*\(\s*MONTH\s+FROM\s+CAST\(\s*\w*\.?NUMJC\s+AS\s+DATE\s*\)", sql, re.IGNORECASE):
        sql = re.sub(
            r"EXTRACT\s*\(\s*MONTH\s+FROM\s+CAST\(\s*(\w*\.*NUMJC)\s+AS\s+DATE\s*\)\s*\)",
            r"SUBSTR(\1, 6, 2)",
            sql,
            flags=re.IGNORECASE
        )
        # Patch SUBSTR(..., 6, 2) = 1, 01, etc. → ... = '01'
        sql = re.sub(
            r"SUBSTR\((\w*\.*NUMJC)\s*,\s*6\s*,\s*2\s*\)\s*=\s*([0-9]{1,2})",
            lambda m: f"SUBSTR({m.group(1)}, 6, 2) = '{int(m.group(2)):02d}'",
            sql
        )
        # Patch quotes autour de l'année
        sql = re.sub(
            r"ANNEE\s*=\s*(\d{4})",
            r"ANNEE = '\1'",
            sql, flags=re.IGNORECASE
        )
        # Ajoute NUMJC IS NOT NULL
        if "NUMJC IS NOT NULL" not in sql.upper():
            sql = re.sub(
                r"\bWHERE\b",
                "WHERE NUMJC IS NOT NULL AND",
                sql,
                count=1,
                flags=re.IGNORECASE
            )

    # Patch taux d'engagement
    sql = re.sub(
        r"SUM\s*\(\s*\w*\.?TAUXENGAGEMENT\s*\)\s*/\s*SUM\s*\(\s*\w*\.?CREDITENGAGE\s*\)",
        "ROUND(100 * SUM(CREDITENGAGE) / NULLIF(SUM(CREDITLFI), 0), 2)",
        sql,
        flags=re.IGNORECASE
    )

    # Patch alias : SUM(V.SITUATION_OP.MONTANTOP) → SUM(MONTANTOP)
    sql = re.sub(r"\b\w+\.\w+\.(\w+)", r"\1", sql)
    sql = re.sub(r"\bV\.(\w+)", r"\1", sql)

    return sql


import re
from typing import Dict, List, Iterable

_VEC_FAMILY = ["vec", "vecc", "veccc", "vec1", "vec2", "vec3", "vec4"]
_LIT = re.compile(r"'.*?'", re.DOTALL)

# FROM/JOIN ... [AS] alias
# - ne matche un alias que s'il existe réellement
# - l'alias ne peut PAS être un mot-clé (WHERE, ORDER, ...)
# - après l'alias, on exige un séparateur/keyword valide (WHERE, JOIN, ',', ')', fin, etc.)
P_FROMJOIN = re.compile(
    r'''(?ix)
    \b(FROM|JOIN)\s+                                   # mot-clé
    (?:\(*\s*)?                                        # parenthèses ouvrantes éventuelles
    (?: "[^"]+"|\w+(?:\s*\.\s*(?:"[^"]+"|\w+))*        # table ou schéma.table
       | \(\s*SELECT\b.*?\)\s*                         # ou sous-requête
    )
    \s+                                                # espace avant alias (s'il existe)
    (?:AS\s+)?                                         # AS optionnel
    (?P<alias>                                         # --- alias candidat
        (?!WHERE\b|JOIN\b|GROUP\b|ORDER\b|HAVING\b|ON\b|USING\b|CONNECT\b|
           LEFT\b|RIGHT\b|FULL\b|OUTER\b|INNER\b|CROSS\b|NATURAL\b|
           UNION\b|MINUS\b|INTERSECT\b|START\b|VALUES\b|SET\b|WITH\b|
           AND\b|OR\b|FETCH\b|OFFSET\b|LIMIT\b|BY\b)
        [A-Z]\w*
    )
    (?=                                                # --- ce qui DOIT suivre l'alias
        \s*(?:,|\)|$                                   # fin d'élément FROM
            |\bWHERE\b|\bJOIN\b|\bON\b|\bUSING\b       # ou mot-clé valide
            |\bGROUP\b|\bORDER\b|\bHAVING\b|\bCONNECT\b|
            \bLEFT\b|\bRIGHT\b|\bFULL\b|\bOUTER\b|\bINNER\b|\bCROSS\b|\bNATURAL\b|
            \bFETCH\b|\bOFFSET\b|\bLIMIT\b
        )
    )
    ''',
    re.DOTALL
)

def _protect_literals(sql: str):
    vals, out, i = [], [], 0
    for m in _LIT.finditer(sql):
        out.append(sql[i:m.start()])
        ph = f"__LIT_{len(vals)}__"
        out.append(ph)
        vals.append(m.group(0))
        i = m.end()
    out.append(sql[i:])
    return "".join(out), vals

def _restore_literals(sql: str, vals: List[str]):
    for i, v in enumerate(vals):
        sql = sql.replace(f"__LIT_{i}__", v)
    return sql

def _gen_vec_name(used: Iterable[str]):
    used = set(a.lower() for a in used)
    for cand in _VEC_FAMILY:
        if cand not in used:
            yield cand
    k = 5
    while True:
        cand = f"vec{k}";  k += 1
        if cand not in used:
            yield cand

def _fix_stray_aliases(work: str, declared_aliases: set) -> str:
    if 'p' not in declared_aliases:
        work = re.sub(r'(?i)(?<!\w)\bp\.(?=[A-Za-z_"])', 'vec.', work)
    if 't' not in declared_aliases:
        work = re.sub(r'(?i)(?<!\w)\bt\.(?=[A-Za-z_"])', 'vec.', work)
    if 'v' not in declared_aliases:
        work = re.sub(r'(?i)(?<!\w)\bv\.(?=[A-Za-z_"])', 'vec.', work)
    if 'o' not in declared_aliases:
        work = re.sub(r'(?i)(?<!\w)\bo\.(?=[A-Za-z_"])', 'vecc.', work)
    return work

def _restore_dropped_group_by(work: str) -> str:
    has_agg = re.search(r'\b(SUM|AVG|COUNT|MIN|MAX)\s*\(', work, flags=re.I)
    if has_agg and not re.search(r'\bGROUP\s+BY\b', work, flags=re.I):
        pattern = re.compile(r'(?i)(?<!GROUP\s)(?<!ORDER\s)(?<!PARTITION\s)(?<!CONNECT\s)\bBY\b')
        work, _ = pattern.subn('GROUP BY', work, count=1)
    return work

def normalize_aliases_to_vec_family(sql: str) -> str:
    original = sql
    work, lits = _protect_literals(original)

    # 1) détecter ONLY si un alias explicite existe (grâce au lookahead)
    aliases_order: List[str] = [m.group('alias') for m in P_FROMJOIN.finditer(work)]
    if not aliases_order:
        return original  # aucun alias => ne touche à rien

    # 2) mapping alias -> vec/vecc/veccc...
    used_now = set(a.lower() for a in aliases_order)
    name_gen = _gen_vec_name(used_now)
    new_names: Dict[str, str] = {}
    for a in aliases_order:
        al = a.lower()
        if al not in new_names:
            new_names[al] = next(name_gen)

    # 3) remplacer les déclarations d’alias
    def repl_fromjoin(m: re.Match) -> str:
        full = m.group(0)
        alias = m.group('alias')
        return (
            full[: m.start('alias') - m.start(0)]
            + new_names[alias.lower()]
            + full[m.end('alias') - m.start(0) :]
        )

    work = P_FROMJOIN.sub(repl_fromjoin, work)

    # 4) remplacer usages "old." par "new."
    for old in sorted(new_names.keys(), key=len, reverse=True):
        new = new_names[old]
        work = re.sub(
            rf'(?i)\b{re.escape(old)}\.(?=[A-Za-z_"])',
            f'{new}.',
            work,
        )

    # 5) alias fantômes éventuels
    declared_final = set(new_names.values())
    work = _fix_stray_aliases(work, declared_final)

    # 6) garde-fou GROUP BY
    work = _restore_dropped_group_by(work)

    # --- fix final contre les doubles alias ---
    def fix_double_alias(sql: str) -> str:
        # supprime les alias du type "vec.TABLE.COL" -> "vec.COL"
        return re.sub(
            r"\b([a-zA-Z0-9_]+)\.[A-Z0-9_]+\.(\w+)",
            r"\1.\2",
            sql,
            flags=re.IGNORECASE,
        )

    return fix_double_alias(_restore_literals(work, lits))



import re

def extract_tables_from_from(sql: str) -> list:
    """
    Récupère toutes les tables mentionnées dans la clause FROM (hors sous-requêtes/jointures complexes).
    Retourne la liste des noms de tables trouvées.
    """
    # On extrait ce qu'il y a entre FROM et WHERE/GROUP BY/ORDER BY/LIMIT ou la fin du string
    from_match = re.search(r"from\s+(.+?)(?:\s+where|\s+group\s+by|\s+order\s+by|\s+limit|;|$)", sql, re.I | re.S)
    if not from_match:
        return []
    from_clause = from_match.group(1)
    # Découpe sur virgules ou jointures simples, puis garde les noms de tables (et alias)
    tables = []
    for part in re.split(r",|\s+join\s+", from_clause):
        table = part.strip().split()[0]
        # Ignore les sous-selects et parenthèses
        if "(" not in table:
            tables.append(table.replace(";", ""))
    return tables

def get_cols_for_tables(tables: list, schema: dict) -> dict:
    """
    Retourne un dict {table: [cols...]} pour chaque table trouvée, si elle existe dans le schéma.
    """
    cols = {}
    for t in tables:
        t0 = t.split('.')[-1]  # pour 'schema.TABLE' → 'TABLE'
        t1 = t0.split()[0]     # pour 'TABLE alias' → 'TABLE'
        if t1 in schema:
            cols[t] = list(schema[t1].keys())
    return cols


def filter_columns_from_schema_dynamic(sql: str, schema: dict) -> str:
    """
    Corrige le SELECT pour ne garder que les colonnes présentes dans le schéma des tables mentionnées dans le FROM/JOIN.
    Corrige aussi le ORDER BY pour ne garder que les colonnes valides.
    Si aucune colonne valide, fallback sur SELECT * FROM ...
    """
    # 1. Filtrage SELECT (identique)
    match = re.match(r"\s*SELECT\s+(.*?)\s+FROM\s", sql, re.I | re.S)
    if not match:
        return sql
    select_cols = match.group(1)
    tables = extract_tables_from_from(sql)
    table_cols = get_cols_for_tables(tables, schema)
    all_allowed = set()
    for cols in table_cols.values():
        all_allowed.update(cols)
    raw_cols = [c.strip() for c in select_cols.split(",")]
    valid_cols = []
    for col in raw_cols:
        # Extrait le vrai nom de colonne (après "AS" et ".")
        c_name = col.split(" as ", 1)[0].split(" AS ", 1)[0].split(".")[-1].strip()
        if c_name in all_allowed:
            valid_cols.append(col)
    # Si rien de valide → SELECT * FROM ...
    if not valid_cols:
        sql = re.sub(r"SELECT\s+.*?\s+FROM", "SELECT * FROM", sql, flags=re.I | re.S)
    else:
        new_select = ", ".join(valid_cols)
        sql = re.sub(r"SELECT\s+.*?\s+FROM", f"SELECT {new_select} FROM", sql, flags=re.I | re.S)
    
    # 2. Nettoyage du ORDER BY
    def order_by_repl(match):
        order_cols = match.group(1)
        cleaned = []
        for col in order_cols.split(","):
            # Retire ASC/DESC pour la vérif
            col_clean = col.replace("ASC", "").replace("DESC", "").strip()
            c_name = col_clean.split(".")[-1].strip()
            if c_name in all_allowed:
                cleaned.append(col.strip())
        if cleaned:
            return "ORDER BY " + ", ".join(cleaned)
        else:
            return ""  # Supprime totalement le ORDER BY si rien n'est valide

    sql = re.sub(r"ORDER BY\s+([^;)\n]+)", order_by_repl, sql, flags=re.I)

    return sql


import re

def escape_all_sql_literals(sql: str) -> str:
    """
    Pour toute valeur entre quotes '...', double les apostrophes internes pour Oracle.
    Exemple: 'dépenses d'investissement' -> 'dépenses d''investissement'
    """
    def repl(m):
        inner = m.group(1)
        # On ne double que les apostrophes simples, pas déjà doublées
        inner_escaped = inner.replace("''", "\x00")  # Place-holder temporaire
        inner_escaped = inner_escaped.replace("'", "''")
        inner_escaped = inner_escaped.replace("\x00", "''")  # On remet les doublées d'origine
        return f"'{inner_escaped}'"

    return re.sub(r"'([^']*)'", repl, sql)



import re

# --- 1) Extrait le "sujet" principal de la question --------------------------
def _extract_subject(question: str) -> str:
    q = question.strip()
    # 1. Prend uniquement la partie entre 'Quels/Quelles' et le verbe
    m = re.match(
        r"(?i)quels?\s+([a-zàâéèêîïôöùûüç\s'\-]+?)(?:\s+(?:ont|sont|est|a|a été|ont été|s’est|se sont|aura|seront|fait|réalisées?|effectuées?|payées?|exécutées?))",
        q
    )
    if m:
        return m.group(1).strip().capitalize()
    # 2. Sinon, extrait après 'Liste des'
    m = re.match(r"(?i)liste\s+des?\s+([a-zàâéèêîïôöùûüç\s'\-]+)", q)
    if m:
        return m.group(1).strip().capitalize()
    # 3. Sinon, extrait après 'Montant des'
    m = re.match(r"(?i)montant\s+des?\s+([a-zàâéèêîïôöùûüç\s'\-]+)", q)
    if m:
        return m.group(1).strip().capitalize()
    # 4. Fallback : prend les premiers mots jusqu’à la 1ère préposition
    m = re.search(r"([a-zàâéèêîïôöùûüç\s'\-]+?)(?:\s+(pour|du|de|des|dans|en|avec|au|aux|par))", q, re.IGNORECASE)
    if m:
        return m.group(1).strip().capitalize()
    # 5. Défaut
    return "résultats"

# --- 2) Extrait rapidement les filtres "col = valeur" du WHERE ---------------
_FILTER_RE = re.compile(
    r"\b([\w$#]+)\s*=\s*'([^']+)'|\b([\w$#]+)\s*=\s*(\d+)",
    re.IGNORECASE
)
def _extract_filters_from_sql(sql: str) -> list[str]:
    filters = []
    for m in _FILTER_RE.finditer(sql):
        col = (m.group(1) or m.group(3)).upper()
        val = m.group(2) or m.group(4)
        filters.append(f"{col} « {val} »")
    return filters

# --- 3) Fonction finale à appeler depuis le pipeline --------------------------
def build_python_natural_multirow_answer(
    question: str,
    columns: list[str],
    rows: list[tuple],
    sql: str
) -> str:
    # 0) Cas aucun résultat
    if not rows:
        return "Aucun résultat trouvé."

    subject = _extract_subject(question)
    filters = _extract_filters_from_sql(sql)

    # Phrase “Les <sujet> [filtre1, filtre2…] sont dans le tableau ci-dessous.”
    filt_str = ""
    if filters:
        # “correspondant à ” + liste filtrée par virgules ou ‘et’
        if len(filters) == 1:
            filt_str = f" correspondant à {filters[0]}"
        else:
            filt_str = " correspondant à " + ", ".join(filters[:-1]) + " et " + filters[-1]

    phrase = f"Les {subject}{filt_str} sont dans le tableau ci-dessous."
    return phrase
