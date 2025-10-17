from pathlib import Path
from collections import defaultdict
import re



N_CTX = 4096 # Contexte pour les modèles LLM
N_GPU_LAYERS = -1 # Nombre de couches à décharger sur le GPU (-1 pour toutes, 0 pour CPU uniquement)


SCHEMA_SQL_detaille = """
CREATE TABLE VECTIS_SITUATION_OP (
    ANNEE              NVARCHAR2(50),    -- Année de l'exercice budgétaire. Format : NUMBER(4)
    NUMOP              NVARCHAR2(50),    -- Numéro de l'ordonnance ou de l'ordre de paiement. Format : VARCHAR2(50)
    DATEOP             DATE,             -- Date de création de l'ordonnance. Permet d'obtenir le mois, l'année ou la période de l'opération. Format : DATE
    HORSJC             CHAR(3),          -- Indique si l'ordonnance est hors Journée Comptable ('Oui' ou 'Non'). Format : VARCHAR2(3)
    TYPEOP             VARCHAR2(23),     -- Type de l'ordonnance ou de l'ordre de paiement. Format : VARCHAR2(50)
    OBJETOP            NVARCHAR2(2000),  -- Description ou objet de l'ordonnance. Format : VARCHAR2(255)
    MONTANTOP          NUMBER,           -- Montant total des ordonnances. Format : NUMBER(20,2)
    BENEFICIAIRE       VARCHAR2(300),    -- Nom du bénéficiaire du paiement. Format : VARCHAR2(255)
    MODEPAIEMENT       NVARCHAR2(50),    -- Mode de paiement utilisé ('Virement', 'Numéraire', '-1', etc.). Format : VARCHAR2(50)
    NUMENGAGEMENT      NVARCHAR2(2000),  -- Référence de l'engagement budgétaire associé. Format : VARCHAR2(50)
    STATUTVALIDATION   VARCHAR2(10),     -- Statut de validation de l'ordonnance ('Validé' ou 'Non validé'). Format : VARCHAR2(50)
    NUMJC              VARCHAR2(200),    -- Numéro de la Journée Comptable (JC). Format : VARCHAR2(50)
    PAYE               CHAR(3),          -- Indique si l'ordonnance a été payée ('Oui' ou 'Non'). Format : VARCHAR2(3)
    DATEPAIEMENT       DATE,             -- Date effective du paiement. Format : DATE
    MISSION            NVARCHAR2(1001),  -- Identifiant ou nom de la mission budgétaire. Format : VARCHAR2(100)
    PROGRAMME          NVARCHAR2(1001),  -- Nom ou identifiant du programme budgétaire c'est le programme. Format : VARCHAR2(100)
    TITRE              NVARCHAR2(1001),  -- Identifiant ou nom du titre budgétaire. Format : VARCHAR2(100)
    CODELIGNE          NVARCHAR2(500),   -- Code de la ligne budgétaire associée. Format : VARCHAR2(50)
    UO                 NVARCHAR2(501),   -- Unité Opérationnelle responsable (identifiant ou nom). Format : VARCHAR2(50)
    PC                 VARCHAR2(601),    -- Poste Comptable (identifiant ou nom). Format : VARCHAR2(50)
    FOURNISSEUR        VARCHAR2(4000),   -- Nom ou identifiant du fournisseur. Format : VARCHAR2(255)
    TYPEENGAGEMENT     VARCHAR2(10)      -- Type d'engagement budgétaire. Format : VARCHAR2(50)
);

CREATE TABLE VECTIS_SITUATION_BUDGET (
    ANNEE                NUMBER,          -- Année de l'exercice budgétaire. Format : NUMBER(4)
    MINISTERE            NVARCHAR2(1001), -- Nom ou identifiant du ministère concerné c'est le ministère. Format : VARCHAR2(100)
    MISSION              NVARCHAR2(1001), -- Nom ou identifiant de la mission budgétaire. Format : VARCHAR2(100)
    PROGRAMME            NVARCHAR2(1001), -- Nom ou identifiant du programme budgétaire c'est le programme. Format : VARCHAR2(100)
    TITRE                NVARCHAR2(1001), -- Nom ou identifiant du titre budgétaire. Format : VARCHAR2(100)
    CODELIGNE            NVARCHAR2(500),  -- Code de la ligne budgétaire . Format : VARCHAR2(100)
    UO                   NVARCHAR2(501),  -- Unité Opérationnelle en charge (nom ou identifiant). Format : VARCHAR2(50)
    PC                   VARCHAR2(601),   -- Poste Comptable concerné (nom ou identifiant). Format : VARCHAR2(50)
    CREDITLFI            NUMBER,          -- Crédits initiaux votés en Loi de Finance Initiale (LFI). Format : NUMBER(20,2)
    CREDITLFR            NUMBER,          -- Crédits rectifiés dans la Loi de Finance Rectificative (LFR). Format : NUMBER(20,2)
    VIREMENT_PLUS_CP     NUMBER,          -- Montant des virements budgétaires entrants (crédits en plus). Format : NUMBER(20,2)
    VIREMENT_MOINS_CP    NUMBER,          -- Montant des virements budgétaires sortants (crédits en moins). Format : NUMBER(20,2)
    CREDITDEFINITIF      NUMBER,          -- Crédits définitifs alloués après ajustements. Format : NUMBER(20,2)
    CREDITDELEGUE        NUMBER,          -- Crédits délégués ou mis à disposition. Format : NUMBER(20,2)
    CREDITENGAGE         NUMBER,          -- Crédits engagé ayant fait l’objet d’un engagement budgétaire. Format : NUMBER(20,2)
    TAUXENGAGEMENT       NUMBER,          -- Taux d’engagement des crédits. Format : NUMBER(3,2)
    CREDITDISPONIBLE     NUMBER,          -- Les crédits disponibles (restants) après engagement. Format : NUMBER(20,2)
    CREDITORDONNANCE     NUMBER,          -- Crédits validés pour ordonnancement. Format : NUMBER(20,2)
    TAUXORDONNANCEMENT   NUMBER,          -- Taux d’ordonnancement. Format : NUMBER(3,2)
    CREDITPAYE           NUMBER,          -- Crédits payés. Format : NUMBER(20,2)
    MOUVEMENTSORTANT     NUMBER,          -- Montant total des virements sortants. Format : NUMBER(20,2)
    MOUVEMENTENTRANT     NUMBER           -- Montant total des virements entrants. Format : NUMBER(20,2)
);
"""

SCHEMA_SQL_detaille_final = """
CREATE TABLE VECTIS_SITUATION_OP (
    ANNEE              NVARCHAR2(50),    -- Année de l'exercice (chaîne). Ex: '2024'. Comparer comme texte: ANNEE = '2024'.
    NUMOP              NVARCHAR2(50),    -- Identifiant de l'ordonnance / ordre de paiement (texte).
    DATEOP             DATE,             -- Date de création de l'ordonnance (pas forcément payée). Peut servir pour mois/année.
    HORSJC             CHAR(3),          -- 'Oui'/'Non' : hors Journée Comptable.
    TYPEOP             VARCHAR2(23),     -- Type d'opération (ex: 'Facture', 'Décompte', 'Avance', 'Ordonnance'). Chaîne courte (<=23).
    OBJETOP            NVARCHAR2(2000),  -- Objet / libellé de l'ordonnance (texte long).
    MONTANTOP          NUMBER,           -- Montant de l'ordonnance (unité monétaire). Utiliser ici pour "montant des ordonnances".
    BENEFICIAIRE       VARCHAR2(300),    -- Bénéficiaire payé (nom/raison sociale).
    MODEPAIEMENT       NVARCHAR2(50),    -- Mode de paiement (ex: 'Virement', 'Numéraire', '-1'...).
    NUMENGAGEMENT      NVARCHAR2(2000),  -- Référence d'engagement budgétaire lié (texte).
    STATUTVALIDATION   VARCHAR2(10),     -- 'Validé' / 'Non validé' (statut de l'ordonnance).
    NUMJC              VARCHAR2(200),    -- Numéro de Journée Comptable.
    PAYE               CHAR(3),          -- 'Oui'/'Non' : indique si l'ordonnance a été effectivement payée.
    DATEPAIEMENT       DATE,             -- Date de paiement effectif. NULLE si PAYE!='Oui'.
    MISSION            NVARCHAR2(1001),  -- Mission budgétaire (texte). Comparer insensiblement à la casse via LOWER(...).
    PROGRAMME          NVARCHAR2(1001),  -- Programme budgétaire (texte). Pour "ordonnances payées par programme", filtrer ici.
    TITRE              NVARCHAR2(1001),  -- Titre budgétaire (texte).
    CODELIGNE          NVARCHAR2(500),   -- Code de ligne budgétaire (clé fonctionnelle). 
    UO                 NVARCHAR2(501),   -- Unité Opérationnelle (texte).
    PC                 VARCHAR2(601),    -- Poste Comptable (texte).
    FOURNISSEUR        VARCHAR2(4000),   -- Fournisseur / tiers (texte).
    TYPEENGAGEMENT     VARCHAR2(10)      -- Type d'engagement (texte court). N'est pas le montant payé.
);

CREATE TABLE VECTIS_SITUATION_BUDGET (
    ANNEE                NUMBER,          -- Année de l'exercice (numérique, 4 chiffres). Ex: 2024. Comparer comme nombre.
    MINISTERE            NVARCHAR2(1001), -- Ministère (texte).
    MISSION              NVARCHAR2(1001), -- Mission budgétaire (texte).
    PROGRAMME            NVARCHAR2(1001), -- Programme budgétaire c'est le programme (texte). Agrégats budgétaires par programme ici.
    TITRE                NVARCHAR2(1001), -- Titre budgétaire (texte).
    CODELIGNE            NVARCHAR2(500),  -- Code de ligne budgétaire. 
    UO                   NVARCHAR2(501),  -- Unité Opérationnelle (texte).
    PC                   VARCHAR2(601),   -- Poste Comptable (texte).
    CREDITLFI            NUMBER,          -- Crédits initiaux (LFI).
    CREDITLFR            NUMBER,          -- Crédits rectifiés (LFR).
    VIREMENT_PLUS_CP     NUMBER,          -- Virements entrants (crédits en plus).
    VIREMENT_MOINS_CP    NUMBER,          -- Virements sortants (crédits en moins).
    CREDITDEFINITIF      NUMBER,          -- Crédits définitifs (après ajustements).
    CREDITDELEGUE        NUMBER,          -- Crédits délégués / mis à disposition.
    CREDITENGAGE         NUMBER,          -- Crédits engagés (engagement budgétaire). Agrégat budgétaire (≠ ordonnances payées).
    TAUXENGAGEMENT       NUMBER,          -- Taux d'engagement (0–100). Agrégat budgétaire.
    CREDITDISPONIBLE     NUMBER,          -- Crédits disponibles (restants).
    CREDITORDONNANCE     NUMBER,          -- Crédits ordonnancés (validés pour ordonnancement). Peut ne pas être payé.
    TAUXORDONNANCEMENT   NUMBER,          -- Taux d’ordonnancement (0–100). Agrégat budgétaire.
    CREDITPAYE           NUMBER,          -- Crédits payés (agrégat budgétaire global). Ne remplace pas MONTANTOP à la pièce.
    MOUVEMENTSORTANT     NUMBER,          -- Total virements sortants.
    MOUVEMENTENTRANT     NUMBER           -- Total virements entrants.
);
"""



SCHEMA_SQL_detaille_table1 = """
CREATE TABLE VECTIS_SITUATION_OP (
    ANNEE              NVARCHAR2(50),    -- Année de l'exercice budgétaire. Format : NUMBER(4)
    NUMOP              NVARCHAR2(50),    -- Numéro de l'ordonnance ou de l'ordre de paiement. Format : VARCHAR2(50)
    DATEOP             DATE,             -- Date de création de l'ordonnance. Permet d'obtenir le mois, l'année ou la période de l'opération. Format : DATE
    HORSJC             CHAR(3),          -- Indique si l'ordonnance est hors Journée Comptable ('Oui' ou 'Non'). Format : VARCHAR2(3)
    TYPEOP             VARCHAR2(23),     -- Type de l'ordonnance ou de l'ordre de paiement. Format : VARCHAR2(50)
    OBJETOP            NVARCHAR2(2000),  -- Description ou objet de l'ordonnance. Format : VARCHAR2(255)
    MONTANTOP          NUMBER,           -- Montant total des ordonnances. Format : NUMBER(20,2)
    BENEFICIAIRE       VARCHAR2(300),    -- Nom du bénéficiaire du paiement. Format : VARCHAR2(255)
    MODEPAIEMENT       NVARCHAR2(50),    -- Mode de paiement utilisé ('Virement', 'Numéraire', '-1', etc.). Format : VARCHAR2(50)
    NUMENGAGEMENT      NVARCHAR2(2000),  -- Référence de l'engagement budgétaire associé. Format : VARCHAR2(50)
    STATUTVALIDATION   VARCHAR2(10),     -- Statut de validation de l'ordonnance ('Validé' ou 'Non validé'). Format : VARCHAR2(50)
    NUMJC              VARCHAR2(200),    -- Numéro de la Journée Comptable (JC). Format : VARCHAR2(50)
    PAYE               CHAR(3),          -- Indique si l'ordonnance a été payée ('Oui' ou 'Non'). Format : VARCHAR2(3)
    DATEPAIEMENT       DATE,             -- Date effective du paiement. Format : DATE
    MISSION            NVARCHAR2(1001),  -- Identifiant ou nom de la mission budgétaire. Format : VARCHAR2(100)
    PROGRAMME          NVARCHAR2(1001),  -- Identifiant ou nom du programme budgétaire. Format : VARCHAR2(100)
    TITRE              NVARCHAR2(1001),  -- Identifiant ou nom du titre budgétaire. Format : VARCHAR2(100)
    CODELIGNE          NVARCHAR2(500),   -- Code de la ligne budgétaire associée. Format : VARCHAR2(50)
    UO                 NVARCHAR2(501),   -- Unité Opérationnelle responsable (uo) (identifiant ou nom). Format : VARCHAR2(50)
    PC                 VARCHAR2(601),    -- Poste Comptable (identifiant ou nom). Format : VARCHAR2(50)
    FOURNISSEUR        VARCHAR2(4000),   -- Nom ou identifiant du fournisseur. Format : VARCHAR2(255)
    TYPEENGAGEMENT     VARCHAR2(10)      -- Type d'engagement budgétaire. Format : VARCHAR2(50)
);
"""


SCHEMA_SQL_detaille_table2 = """ 
CREATE TABLE VECTIS_SITUATION_BUDGET (
    ANNEE                NUMBER,          -- Année de l'exercice budgétaire. Format : NUMBER(4)
    MINISTERE            NVARCHAR2(1001), -- Nom ou identifiant du ministère concerné. Format : VARCHAR2(100)
    MISSION              NVARCHAR2(1001), -- Nom ou identifiant de la mission budgétaire. Format : VARCHAR2(100)
    PROGRAMME            NVARCHAR2(1001), -- Nom ou identifiant du programme budgétaire c'est le programme. Format : VARCHAR2(100)
    TITRE                NVARCHAR2(1001), -- Nom ou identifiant du titre budgétaire. Format : VARCHAR2(100)
    CODELIGNE            NVARCHAR2(500),  -- Code de la ligne budgétaire . Format : VARCHAR2(100)
    UO                   NVARCHAR2(501),  -- Unité Opérationnelle en charge (uo) (nom ou identifiant). Format : VARCHAR2(50)
    PC                   VARCHAR2(601),   -- Poste Comptable concerné (nom ou identifiant). Format : VARCHAR2(50)
    CREDITLFI            NUMBER,          -- Crédits initiaux votés en Loi de Finance Initiale (LFI). Format : NUMBER(20,2)
    CREDITLFR            NUMBER,          -- Crédits rectifiés dans la Loi de Finance Rectificative (LFR). Format : NUMBER(20,2)
    VIREMENT_PLUS_CP     NUMBER,          -- Montant des virements budgétaires entrants (crédits en plus). Format : NUMBER(20,2)
    VIREMENT_MOINS_CP    NUMBER,          -- Montant des virements budgétaires sortants (crédits en moins). Format : NUMBER(20,2)
    CREDITDEFINITIF      NUMBER,          -- Crédits définitifs alloués après ajustements. Format : NUMBER(20,2)
    CREDITDELEGUE        NUMBER,          -- Crédits délégués ou mis à disposition. Format : NUMBER(20,2)
    CREDITENGAGE         NUMBER,          -- Crédits ayant fait l’objet d’un engagement budgétaire. Format : NUMBER(20,2)
    TAUXENGAGEMENT       NUMBER,          -- Taux d’engagement des crédits. Format : NUMBER(3,2)
    CREDITDISPONIBLE     NUMBER,          -- Les crédits disponibles (restants) après engagement. Format : NUMBER(20,2)
    CREDITORDONNANCE     NUMBER,          -- Crédits validés pour ordonnancement. Format : NUMBER(20,2)
    TAUXORDONNANCEMENT   NUMBER,          -- Taux d’ordonnancement. Format : NUMBER(3,2)
    CREDITPAYE           NUMBER,          -- Crédits effectivement payés. Format : NUMBER(20,2)
    MOUVEMENTSORTANT     NUMBER,          -- Montant total des virements sortants. Format : NUMBER(20,2)
    MOUVEMENTENTRANT     NUMBER           -- Montant total des virements entrants. Format : NUMBER(20,2)
);
"""


# Démarreurs SQL pour la validation
SQL_STARTERS = ("SELECT", "WITH", "INSERT", "UPDATE", "DELETE", "CREATE", "ALTER", "DROP")

# Configuration de la sélection des candidats SQL
USE_SQL_CANDIDATE_SELECTION = True
NUM_SQL_CANDIDATES = 5

# === Mapping d'abréviations métiers vers libellés explicites ===
ALIAS_MAPPING = {
    "UO": "Unité Opérationnelle",
    "PC": "Poste Comptable",
    "JC": "Journal de Caisse",
    "TYPEOP": "Type d’Opération",
    "OBJETOP": "Objet de l’Opération",
    "MODEPAIEMENT": "Mode de Paiement",
    "STATUTVALIDATION": "Statut de Validation",
    "PAYE": "Statut de Paiement",
    "DATEPAIEMENT": "Date de Paiement",
    "TYPEENGAGEMENT": "Type d’Engagement",
    "MONTANTOP": "Montant de l’Opération",
    "CREDITLFI": "Crédit de la Loi de Finances Initiale",
    "CREDITLFR": "Crédit de la Loi de Finances Rectificative",
    "CREDITDEFINITIF": "Crédit Définitif",
    "CREDITDELEGUE": "Crédit Délégué",
    "CREDITENGAGE": "Crédit Engagé",
    "CREDITDISPONIBLE": "Crédit Disponible",
    "CREDITORDONNANCE": "Crédit Ordonnancé",
    "CREDITPAYE": "Crédit Payé",
    "TAUXENGAGEMENT": "Taux d’Engagement",
    "TAUXORDONNANCEMENT": "Taux d’Ordonnancement",
    "MOUVEMENTSORTANT": "Mouvements Sortants",
    "MOUVEMENTENTRANT": "Mouvements Entrants",
    "BENEFICIAIRE": "Bénéficiaire",
    "FOURNISSEUR": "Fournisseur",
    "TITRE": "Titre Budgétaire",
    "MISSION": "Mission Budgétaire",
    "PROGRAMME": "Programme Budgétaire",
    "CODELIGNE": "Code Ligne",
    "NUMJC": "Numéro Journal Caisse",
    "NUMENGAGEMENT": "Numéro Engagement"
}

def parse_schema(sql: str):
    tables = defaultdict(dict)
    current_table = None
    for line in sql.splitlines():
        table_match = re.match(r"CREATE TABLE (\w+)", line, re.I)
        if table_match:
            current_table = table_match.group(1)
            continue

        if current_table:
            column_match = re.match(r"\s*(\w+)\s+([\w\(\)]+)", line)
            if column_match:
                col_name, col_type = column_match.groups()
                tables[current_table][col_name] = col_type
    return dict(tables)

