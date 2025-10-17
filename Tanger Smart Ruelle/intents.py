from enum import Enum

class Intent(str, Enum):
    RESTAURANT = "restaurant"
    MONUMENT   = "monument"
    ITINERAIRE = "itineraire"
    HORAIRE    = "horaire"
    TRADUCTION = "traduction"
    AUTRE      = "autre"
    NEGATIF    = "negatif"

INTENT_LABELS = [i.value for i in Intent]
