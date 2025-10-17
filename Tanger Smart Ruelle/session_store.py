import uuid
from typing import List, Dict

_STORE: Dict[str, List[dict]] = {}

def create_session() -> str:
    sid = uuid.uuid4().hex
    _STORE[sid] = []
    return sid

def load_history(session_id: str) -> List[dict]:
    return _STORE.get(session_id, [])

def save_history(session_id: str, history: List[dict]):
    _STORE[session_id] = history

def delete_session(session_id: str):
    _STORE.pop(session_id, None)
