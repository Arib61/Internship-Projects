import re
from typing import Callable, Pattern, Tuple, Union, Optional

# ---------------------------------------------------------------------------
#  UTILITAIRES GÉNÉRIQUES
# ---------------------------------------------------------------------------

import re

def _replace_extract_week(sql: str) -> str:
    """
    Oracle 11g ne supporte pas EXTRACT(WEEK FROM ...).
    Convertit en TO_CHAR(expr, 'IW') (numéro de semaine ISO).
    Gère aussi DATE_PART('week', expr).
    """
    if not sql or not isinstance(sql, str):
        return sql

    # DATE_PART('week', expr)  -> TO_CHAR(expr, 'IW')
    sql = re.sub(
        r"(?is)DATE_PART\(\s*'week'\s*,\s*([^)]+?)\s*\)",
        r"TO_CHAR(\1,'IW')",
        sql
    )

    # EXTRACT(WEEK FROM expr) -> TO_CHAR(expr, 'IW')
    sql = re.sub(
        r"(?is)EXTRACT\s*\(\s*WEEK\s+FROM\s+([^)]+?)\s*\)",
        r"TO_CHAR(\1,'IW')",
        sql
    )

    return sql


def _promote_year_to_isoyear_when_week(sql: str) -> str:
    """
    Si la requête utilise des semaines ISO ('IW'), on remplace EXTRACT(YEAR FROM expr)
    par TO_CHAR(expr,'IYYY') pour éviter les erreurs autour des semaines chevauchant l'année.
    Heuristique conservatrice sur les formes simples.
    """
    if not sql or "IW" not in sql.upper():
        return sql

    # EXTRACT(YEAR FROM <identifiant simple>) -> TO_CHAR(<identifiant>,'IYYY')
    # (identifiant simple = [schéma.]colonne sans parenthèses)
    def repl_year(m: re.Match) -> str:
        expr = m.group(1).strip()
        # On évite de toucher les expressions complexes (contiennent '(' ou ')')
        if "(" in expr or ")" in expr:
            return m.group(0)
        return f"TO_CHAR({expr},'IYYY')"

    sql = re.sub(
        r"(?is)EXTRACT\s*\(\s*YEAR\s+FROM\s+([A-Z0-9_\.]+)\s*\)",
        repl_year,
        sql
    )

    return sql



def _sub(pattern: Union[str, Pattern], repl: Union[str, Callable], text: str, flags=0) -> str:
    """Wrapper pour re.sub qui ignore les erreurs de regex."""
    try:
        return re.sub(pattern, repl, text, flags=flags)
    except re.error:
        return text

def _strip_semicolon(sql: str) -> str:
    return sql.rstrip().rstrip(";")

def _normalize_whitespace(sql: str) -> str:
    return re.sub(r"\s+", " ", sql).strip()

# ---------------------------------------------------------------------------
#  HELPERS
# ---------------------------------------------------------------------------

_BLOCKED_SQL = "SELECT 'Not allowed' AS MSG FROM dual"
_DUMMY_SQL   = "SELECT 'Not possible' AS MSG FROM dual"

_DML_DDL_RE  = re.compile(r"\b(DROP|DELETE|ALTER|UPDATE|INSERT|TRUNCATE|MERGE|CREATE|GRANT|REVOKE)\b", re.IGNORECASE)

def _has_multi_statements(sql: str) -> bool:
    # grossier : plusieurs ';' internes (hors fin)
    return sql.count(";") > 1

def _is_select_like(sql: str) -> bool:
    return bool(re.match(r"^\s*(WITH|SELECT)\b", sql, re.IGNORECASE))

def _extract_limit_offset(sql: str) -> Tuple[Optional[int], Optional[int], str]:
    """
    Retourne (limit, offset, sql_sans_clause) pour:
      - LIMIT n [OFFSET m]
      - OFFSET m LIMIT n
      - FETCH FIRST n ROWS ONLY
    """
    limit = offset = None
    work = sql

    # FETCH FIRST n ROWS ONLY
    m = re.search(r"\bFETCH\s+FIRST\s+(\d+)\s+ROWS\s+ONLY\b", work, re.IGNORECASE)
    if m:
        limit = int(m.group(1))
        work  = _sub(r"\bFETCH\s+FIRST\s+\d+\s+ROWS\s+ONLY\b", "", work, flags=re.IGNORECASE)

    # LIMIT n [OFFSET m]
    m = re.search(r"\bLIMIT\s+(\d+)\s*(OFFSET\s+(\d+))?", work, re.IGNORECASE)
    if m:
        limit = int(m.group(1))
        if m.group(3): offset = int(m.group(3))
        work = _sub(r"\bLIMIT\s+\d+\s*(OFFSET\s+\d+)?", "", work, flags=re.IGNORECASE)

    # OFFSET m LIMIT n (ordre inversé)
    m = re.search(r"\bOFFSET\s+(\d+)\s*LIMIT\s+(\d+)", work, re.IGNORECASE)
    if m:
        offset = int(m.group(1))
        limit  = int(m.group(2))
        work   = _sub(r"\bOFFSET\s+\d+\s*LIMIT\s+\d+", "", work, flags=re.IGNORECASE)

    # OFFSET m seul -> Oracle 11 ne sait pas; on va juste ignorer / wrap avec rownum
    m = re.search(r"\bOFFSET\s+(\d+)", work, re.IGNORECASE)
    if m and offset is None:
        offset = int(m.group(1))
        work   = _sub(r"\bOFFSET\s+\d+", "", work, flags=re.IGNORECASE)

    return limit, offset, work.strip()

def _wrap_with_rownum(sql_core: str, limit: Optional[int], offset: Optional[int]) -> str:
    """
    Enveloppe la requête avec ROWNUM pour simuler LIMIT / OFFSET (Oracle 11g).
    """
    if limit is None and offset is None:
        return sql_core  # rien à faire

    # Oracle 11g : ROWNUM commence à 1
    if offset is None: offset = 0
    if limit is None:
        # OFFSET seul : juste ignorer les 'offset' premières lignes
        # -> SELECT * FROM ( SELECT a.*, ROWNUM r__ FROM (sql_core) a ) WHERE r__ > offset
        return f"SELECT * FROM ( SELECT a.*, ROWNUM r__ FROM ( {sql_core} ) a ) WHERE r__ > {offset}"

    # limit+offset
    upper_bound = offset + limit
    if offset == 0:
        # simple LIMIT
        return f"SELECT * FROM ( {sql_core} ) WHERE ROWNUM <= {limit}"
    else:
        # LIMIT + OFFSET
        return (
            "SELECT * FROM ( "
            f"SELECT a.*, ROWNUM r__ FROM ( {sql_core} ) a WHERE ROWNUM <= {upper_bound} "
            ") WHERE r__ > {offset}"
        )

def _convert_cast(match: re.Match) -> str:
    expr = match.group(1)
    typ  = match.group(2).lower()
    if typ in ("float", "double", "double precision", "numeric", "number", "int", "integer", "bigint", "smallint", "decimal", "real"):
        return f"TO_NUMBER({expr})"
    elif typ in ("text", "varchar", "varchar2", "char", "character varying"):
        return f"TO_CHAR({expr})"
    elif typ in ("date", "timestamp", "timestamptz"):
        return f"CAST({expr} AS DATE)"
    else:
        # On enlève juste le CAST, Oracle peut gérer certains
        return expr

def _convert_pg_cast_syntax(text: str) -> str:
    # gestion "expr::type"
    def repl(m):
        expr, typ = m.group(1), m.group(2).lower()
        if typ in ("float", "double", "double precision", "numeric", "number", "int", "integer", "bigint", "smallint", "decimal", "real"):
            return f"TO_NUMBER({expr})"
        elif typ in ("text", "varchar", "varchar2", "char", "character varying"):
            return f"TO_CHAR({expr})"
        elif typ in ("date", "timestamp", "timestamptz"):
            return f"CAST({expr} AS DATE)"
        else:
            return expr

    text = re.sub(r"(\S+?)::\s*([A-Za-z_][A-Za-z0-9_]*)", repl, text)
    return text

# ---------------------------------------------------------------------------
#  FONCTION PRINCIPALE
# ---------------------------------------------------------------------------

def sanitize_sql_for_oracle(sql: str) -> str:
    """
    Transforme / “Oracle-ise” une requête SQL issue d’un LLM (souvent dialecte PostgreSQL)
    pour qu’elle s’exécute sur Oracle 11g.
    """
    if not sql:
        return _DUMMY_SQL + ";"

    sql = sql.strip()

    # 0. Bloquer DML/DDL et multi-statements
    if _DML_DDL_RE.search(sql) or _has_multi_statements(sql):
        return _BLOCKED_SQL + ";"

    # 1. S'assurer qu'on est sur une requête SELECT/WITH
    if not _is_select_like(sql):
        return _DUMMY_SQL + ";"

    # 2. Retirer le ; final pour travailler proprement
    sql = _strip_semicolon(sql)

    # 3. Transformations génériques
    # -----------------------------------------------------------------------
    # Ordre important : on veut d'abord virer les syntaxes qui cassent le parsing
    # -----------------------------------------------------------------------

    # 3.1 Remplacer les casts Postgres "::type"
    sql = _convert_pg_cast_syntax(sql)

    # 3.2 CAST( ... AS TYPE )
    sql = _sub(
        re.compile(r"CAST\s*\(\s*([^)]+?)\s+AS\s+([A-Za-z_][A-Za-z0-9_]*)\s*\)", re.IGNORECASE),
        _convert_cast,
        sql
    )

    # 3.3 ILIKE -> LOWER(x) LIKE LOWER('pattern')
    # gère "expr ILIKE 'xxx'"
    sql = _sub(
        re.compile(r"(?i)([^'\s]+)\s+ILIKE\s+'([^']*)'"),
        lambda m: f"LOWER({m.group(1)}) LIKE LOWER('{m.group(2)}')",
        sql
    )
    # fallback : remplacer ILIKE par LIKE si on a raté un cas
    sql = _sub(r"\bILIKE\b", "LIKE", sql, flags=re.IGNORECASE)

    # 3.4 LOWER/UPPER mal placés
    sql = _sub(
        re.compile(r"(\w+)\.(LOWER|UPPER)\((\w+)\)", re.IGNORECASE),
        lambda m: f"{m.group(2).upper()}({m.group(1)}.{m.group(3)})",
        sql
    )
    sql = _sub(
        re.compile(r"(LOWER|UPPER)\((\w+)\.(\w+)\)", re.IGNORECASE),
        lambda m: f"{m.group(1).upper()}({m.group(2)}.{m.group(3)})",
        sql
    )

    # 3.5 DATE_TRUNC('month', col) / ('year', ...)
    sql = _sub(
        re.compile(r"DATE_TRUNC\(\s*'month'\s*,\s*([^)]+)\)", re.IGNORECASE),
        r"TRUNC(\1,'MM')",
        sql
    )
    sql = _sub(
        re.compile(r"DATE_TRUNC\(\s*'year'\s*,\s*([^)]+)\)", re.IGNORECASE),
        r"TRUNC(\1,'YYYY')",
        sql
    )

    # 3.6 DATE_PART / EXTRACT  (Postgres style)
    sql = _sub(
        re.compile(r"DATE_PART\(\s*'year'\s*,\s*([^)]+)\)", re.IGNORECASE),
        r"EXTRACT(YEAR FROM \1)",
        sql
    )
    sql = _sub(
        re.compile(r"DATE_PART\(\s*'month'\s*,\s*([^)]+)\)", re.IGNORECASE),
        r"EXTRACT(MONTH FROM \1)",
        sql
    )
    sql = _sub(
        re.compile(r"DATE_PART\(\s*'day'\s*,\s*([^)]+)\)", re.IGNORECASE),
        r"EXTRACT(DAY FROM \1)",
        sql
    )

    # EXTRACT(YEAR FROM ...) etc → Oracle 11g ok, on laisse, mais nos remplacements plus haut ont tout mis en TO_CHAR.
    # Tu peux décider d'uniformiser en TO_CHAR si tu préfères.

    # 3.7 BOOLEAN literals
    sql = _sub(r"\bTRUE\b",  "'1'", sql, flags=re.IGNORECASE)
    sql = _sub(r"\bFALSE\b", "'0'", sql, flags=re.IGNORECASE)

    # 3.8 CURRENT_DATE / CURRENT_TIMESTAMP
    sql = _sub(r"\bCURRENT_DATE\b",      "SYSDATE",      sql, flags=re.IGNORECASE)
    sql = _sub(r"\bCURRENT_TIMESTAMP\b", "SYSTIMESTAMP", sql, flags=re.IGNORECASE)
    sql = _sub(r"\bNOW\(\)",             "SYSTIMESTAMP", sql, flags=re.IGNORECASE)

    # 3.9 NULLS FIRST / LAST (inutile en 11g)
    sql = _sub(r"NULLS\s+(FIRST|LAST)", "", sql, flags=re.IGNORECASE)

    # 3.10 CAST(... AS FLOAT) déjà traité, mais on double-link
    sql = _sub(
        re.compile(r"CAST\(([^)]+?)\s+AS\s+FLOAT\)", re.IGNORECASE),
        r"TO_NUMBER(\1)",
        sql
    )

    # 3.11 OFFSET or LIMIT handled later in wrapping, but remove duplicates
    # 3.12 DOUBLE %% -> %
    sql = _sub(r"%{2,}", "%", sql)

    # 3.13 Doublons de virgules
    sql = _sub(r",\s*,+", ",", sql)

    # 3.14 STRING_AGG(expr, ',') -> LISTAGG(expr, ',') WITHIN GROUP (ORDER BY 1)
    sql = _sub(
        re.compile(r"STRING_AGG\s*\(\s*([^\),]+)\s*,\s*'([^']*)'\s*\)", re.IGNORECASE),
        r"LISTAGG(\1, '\2') WITHIN GROUP (ORDER BY 1)",
        sql
    )

    # 3.15 DISTINCT ON (cols) -> à convertir; Oracle n'a pas. On peut simuler avec analytic, mais ici on supprime ON
    sql = _sub(r"\bDISTINCT\s+ON\s*\(\s*[^)]+\)", "DISTINCT", sql, flags=re.IGNORECASE)

    # 3.16 "::float" ou "::text" restants
    sql = _sub(r"::\s*\w+", "", sql)

    # -----------------------------------------------------------------------
    # 4. Gestion LIMIT / OFFSET / FETCH FIRST
    # -----------------------------------------------------------------------
    limit, offset, core = _extract_limit_offset(sql)
    sql = _wrap_with_rownum(core, limit, offset)


    def _patch_iso_dates(s: str) -> str:
        """
        Remplace :   COL_DATE  = '2024-08-08'
        par :        COL_DATE  = TO_DATE('2024-08-08','YYYY-MM-DD')

        ▸ Marche avec =, <>, >, <, >=, <=  
        ▸ Marche pour n’importe quel nom de colonne contenant DATE
        ▸ Marche avec ou sans alias devant (ex : vop.DATEOP, DATEOP)
        ▸ Marche avec IN (...)
        """
        # 1. opérateurs simples (=, <>, >, <, >=, <=)
        s = re.sub(
            r"(\b(?:\w+\.)?\w*DATE\w*\b)\s*([<>]=?|=)\s*'(\d{4}-\d{2}-\d{2})'",
            r"\1 \2 TO_DATE('\3','YYYY-MM-DD')",
            s,
            flags=re.IGNORECASE,
        )

        # 2. IN (...)  ex.  DATEOP IN ('2024-08-08','2024-08-09')
        def _in_repl(m):
            col, dates = m.group(1), m.group(2)
            new_list = ", ".join(
                f"TO_DATE('{d}','YYYY-MM-DD')" for d in re.findall(r"\d{4}-\d{2}-\d{2}", dates)
            )
            return f"{col} IN ({new_list})"

        s = re.sub(
            r"(\b(?:\w+\.)?\w*DATE\w*\b)\s+IN\s*\(([^)]*?\d{4}-\d{2}-\d{2}[^)]*)\)",
            _in_repl,
            s,
            flags=re.IGNORECASE,
        )
        return s


    sql = _patch_iso_dates(sql)

    # -----------------------------------------------------------------------
    # 5. Nettoyage final + point-virgule
    # -----------------------------------------------------------------------
    sql = _normalize_whitespace(sql)

    # 3.6.b Semaine (Oracle 11g) : EXTRACT(WEEK ...) / DATE_PART('week', ...)
    sql = _replace_extract_week(sql)

    # 3.6.c Si on manipule des semaines ISO, utiliser l'année ISO (IYYY) plutôt que YEAR
    sql = _promote_year_to_isoyear_when_week(sql)


    if not sql.endswith(";"):
        sql += ";"

    return sql

import re

def patch_tauxengagement_sql(sql: str) -> str:
    """
    Remplace le SELECT TAUXENGAGEMENT (avec ou sans alias) par le calcul métier,
    et n'ajoute pas d'alias si déjà présent.
    """
    # Pattern: SELECT [optional alias.]TAUXENGAGEMENT (insensible à la casse)
    pattern = re.compile(r"SELECT\s+((\w+)\.)?TAUXENGAGEMENT", re.IGNORECASE)
    
    def repl(match):
        # Si alias existe (ex: VSU.), n’ajoute pas d’alias dans le champ calculé
        return "SELECT 100 * SUM(CREDITENGAGE) / NULLIF(SUM(CREDITDELEGUE), 0) AS TAUX_ENGAGEMENT_GLOBAL"
    
    # Remplacement uniquement sur le premier SELECT
    return pattern.sub(repl, sql, count=1)


import re

def normalize_temporal_literals(sql: str, *, eod=True) -> str:
    if not isinstance(sql, str) or not sql.strip():
        return sql

    lit_re = re.compile(
        r"'(\d{4}[-/.]\d{2}[-/.]\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?)'"
    )

    def _parse_core(core: str):
        core = core.replace('T', ' ')
        core = core.replace('/', '-').replace('.', '-')
        m = re.fullmatch(r'(\d{4}-\d{2}-\d{2})(?:\s+(\d{2}:\d{2}(?::\d{2})?))?', core)
        if not m:
            return None, None
        d, t = m.group(1), m.group(2)
        if t is None:
            return d, None
        if re.fullmatch(r'\d{2}:\d{2}$', t):
            t += ':00'
        return d, t

    def _to_to_date(core: str, default_to_eod: bool) -> str:
        d, t = _parse_core(core)
        if d is None:
            return f"'{core}'"
        if t is None:
            t = '23:59:59' if default_to_eod else '00:00:00'
        return f"TO_DATE('{d} {t}','YYYY-MM-DD HH24:MI:SS')"

    def _repl_literal(lit: str, default_to_eod: bool) -> str:
        m = lit_re.fullmatch(lit)
        if not m:
            return lit
        return _to_to_date(m.group(1), default_to_eod)

    # --- 1) BETWEEN ---
    between_pattern = re.compile(
        r"(\bBETWEEN\s+(?:DATE|TIMESTAMP\s*)?'.+?')\s+AND\s+((?:DATE|TIMESTAMP\s*)?'.+?')",
        flags=re.IGNORECASE
    )

    def _fix_between(m):
        left_raw = m.group(1)
        right_raw = m.group(2)
        # extraire la quote seule
        left_quote = re.search(r"'[^']*'", left_raw).group(0)
        right_quote = re.search(r"'[^']*'", right_raw).group(0)
        left_fixed = _repl_literal(left_quote, False)
        right_fixed = _repl_literal(right_quote, eod)
        return m.group(0).replace(left_quote, left_fixed).replace(right_quote, right_fixed)

    s = between_pattern.sub(_fix_between, sql)

    # --- 2) Comparaisons col OP 'lit'
    cmp_right_pattern = re.compile(
        r"(\b[A-Z0-9_\"$#]+\b(?:\s*\.\s*\b[A-Z0-9_\"$#]+\b)?)\s*(>=|<=|=|<>|>|<)\s*((?:DATE|TIMESTAMP\s*)?'[^']*')",
        flags=re.IGNORECASE
    )

    def _fix_cmp_right(m):
        op = m.group(2)
        default_to_eod = eod and op in ('<', '<=')
        lit = re.search(r"'[^']*'", m.group(3)).group(0)
        lit_fixed = _repl_literal(lit, default_to_eod)
        return m.group(1) + " " + op + " " + m.group(3).replace(lit, lit_fixed)

    s = cmp_right_pattern.sub(_fix_cmp_right, s)

    # --- 3) Comparaisons inversées 'lit' OP col
    cmp_left_pattern = re.compile(
        r"((?:DATE|TIMESTAMP\s*)?'[^']*')\s*(>=|<=|=|<>|>|<)\s*(\b[A-Z0-9_\"$#]+\b(?:\s*\.\s*\b[A-Z0-9_\"$#]+\b)?)",
        flags=re.IGNORECASE
    )

    def _fix_cmp_left(m):
        op = m.group(2)
        default_to_eod = eod and op in ('>', '>=')
        lit = re.search(r"'[^']*'", m.group(1)).group(0)
        lit_fixed = _repl_literal(lit, default_to_eod)
        return m.group(1).replace(lit, lit_fixed) + " " + op + " " + m.group(3)

    s = cmp_left_pattern.sub(_fix_cmp_left, s)

    return s



import re
import unicodedata
from typing import Dict, Tuple, List

SQL_KEYWORDS = {
    "SELECT","FROM","WHERE","GROUP","BY","ORDER","HAVING","JOIN","LEFT","RIGHT","FULL","OUTER","INNER",
    "ON","USING","CONNECT","UNION","MINUS","INTERSECT","START","WITH","AND","OR","NOT","NULL","IS","IN",
    "LIKE","BETWEEN","EXISTS","CASE","WHEN","THEN","ELSE","END","AS","FETCH","OFFSET","LIMIT","DISTINCT","ALL","UNIQUE"
}

def _strip_quotes(s: str) -> Tuple[str, bool]:
    if s.startswith('"') and s.endswith('"'):
        return s[1:-1], True
    return s, False

def _remove_accents(s: str) -> str:
    nkfd = unicodedata.normalize('NFKD', s)
    return ''.join(c for c in nkfd if not unicodedata.combining(c))

def _shorten_alias(raw: str, max_len: int = 30) -> str:
    s = _remove_accents(raw)
    s = re.sub(r'[^A-Za-z0-9_]+', '_', s).strip('_')
    parts = [p for p in s.split('_') if p]
    if not parts:
        parts = ['COL']
    short = '_'.join(parts[:2])
    if len(short) > max_len:
        short = short[:max_len]
    return short or 'COL'

def _split_select_items(select_text: str) -> List[str]:
    # split par virgules au niveau 0 (hors parenthèses / guillemets)
    items, buf, depth, in_s, in_d = [], [], 0, False, False
    i, n = 0, len(select_text)
    while i < n:
        ch = select_text[i]
        if ch == "'" and not in_d:
            in_s = not in_s
        elif ch == '"' and not in_s:
            in_d = not in_d
        elif not in_s and not in_d:
            if ch == '(':
                depth += 1
            elif ch == ')':
                depth = max(0, depth-1)
            elif ch == ',' and depth == 0:
                items.append(''.join(buf).strip())
                buf = []
                i += 1
                continue
        buf.append(ch)
        i += 1
    if buf:
        items.append(''.join(buf).strip())
    return items

def _parse_item_alias(item: str):
    """
    Retourne (expr, alias, alias_was_quoted) si alias détecté, sinon (item, None, False).
    Ne considère pas DISTINCT/ALL/UNIQUE comme une 'expr'.
    """
    s = item.strip()
    # retirer éventuels parenthèses externes inutiles (rare)
    # 1) avec AS
    m = re.match(r'(?is)^(?P<expr>.+?)\s+AS\s+(?P<alias>"[^"]+"|[A-Za-z_]\w*)\s*$', s)
    if m:
        expr = m.group('expr').strip()
        alias_raw = m.group('alias').strip()
        alias, was_q = _strip_quotes(alias_raw)
        if alias.upper() in SQL_KEYWORDS:
            return (item, None, False)
        return (expr, alias, was_q)

    # 2) alias quoted à la fin (sans AS)
    m = re.match(r'(?is)^(?P<expr>.+?)\s+(?P<alias>"[^"]+")\s*$', s)
    if m:
        expr = m.group('expr').strip()
        alias_raw = m.group('alias').strip()
        alias, was_q = _strip_quotes(alias_raw)
        if alias.upper() in SQL_KEYWORDS:
            return (item, None, False)
        # éviter de prendre "DISTINCT col" pour expr+alias
        if expr.upper() in {"DISTINCT","ALL","UNIQUE"}:
            return (item, None, False)
        return (expr, alias, was_q)

    # 3) alias non-quoté à la fin (sans AS)
    m = re.match(r'(?is)^(?P<expr>.+?)\s+(?P<alias>[A-Za-z_]\w*)\s*$', s)
    if m:
        expr = m.group('expr').strip()
        alias = m.group('alias').strip()
        # ne pas confondre DISTINCT/ALL/UNIQUE <col> avec expr+alias
        if expr.upper() in {"DISTINCT","ALL","UNIQUE"}:
            return (item, None, False)
        if alias.upper() in SQL_KEYWORDS:
            return (item, None, False)
        return (expr, alias, False)

    # aucun alias
    return (item, None, False)

def _make_clause_region_pattern(kw: str) -> re.Pattern:
    return re.compile(
        rf'(?is)\b{kw}\b(.*?)(?:(?:\bORDER\b|\bGROUP\b|\bHAVING\b|\bFETCH\b|\bOFFSET\b|\bLIMIT\b|\bFOR\b|\)|$))'
    )

CLAUSE_PATTERNS = {
    'ORDER BY': _make_clause_region_pattern('ORDER BY'),
    'GROUP BY': _make_clause_region_pattern('GROUP BY'),
    'HAVING':   _make_clause_region_pattern('HAVING'),
}

def shorten_sql_aliases(sql: str, max_len: int = 30) -> str:
    m = re.search(r'(?is)\bSELECT\b(.*?)\bFROM\b', sql)
    if not m:
        return sql

    select_clause = m.group(1)
    start, end = m.span(1)

    # isole DISTINCT|ALL|UNIQUE au début de la SELECT-list
    lead_kw = ''
    mlead = re.match(r'(?is)^\s*(DISTINCT|ALL|UNIQUE)\b(.*)$', select_clause)
    if mlead:
        lead_kw = mlead.group(1).upper() + ' '
        select_body = mlead.group(2).strip()
    else:
        select_body = select_clause.strip()

    # découpe en items de select
    items = _split_select_items(select_body)

    alias_map: Dict[str, str] = {}
    used: Dict[str, int] = {}

    def unique(name: str) -> str:
        base = name
        if base not in used:
            used[base] = 1
            return base
        i = used[base]
        while True:
            i += 1
            cand = f'{base}_{i}'
            if cand not in used:
                used[base] = i
                used[cand] = 1
                return cand

    new_items = []
    for it in items:
        expr, alias, was_q = _parse_item_alias(it)
        if alias is None:
            # pas d’alias => ne rien changer pour cet item
            new_items.append(it)
            continue
        # raccourcir l’alias existant
        new_alias = unique(_shorten_alias(alias, max_len=max_len))
        alias_map[alias] = new_alias
        new_items.append(f"{expr} AS {new_alias}")

    # si aucun alias dans la SELECT-list => ne rien faire du tout
    if not alias_map:
        return sql

    # reconstruit la SELECT-list et la requête
    new_select = lead_kw + ', '.join(new_items)
    new_sql = sql[:start] + ' ' + new_select + ' ' + sql[end:]

    # mettre à jour ORDER/GROUP/HAVING quand ils référencent ces alias
    def replace_in_clause(pattern: re.Pattern, text: str) -> str:
        def sub_clause(mc):
            block = mc.group(0)
            for old, new in alias_map.items():
                # remplace "old" avec/ sans quotes
                block = re.sub(rf'"{re.escape(old)}"', new, block)
                block = re.sub(rf'\b{re.escape(old)}\b', new, block)
            return block
        return pattern.sub(sub_clause, text)

    for _, pat in CLAUSE_PATTERNS.items():
        new_sql = replace_in_clause(pat, new_sql)

    return new_sql
