import redis
import uuid
import json

# --- CONFIG REDIS ---
redis_client = redis.Redis(host='localhost', port=6379, db=0)  # Modifie l’host/port si besoin
SESSION_TTL = 3600  # secondes (1h)

def create_session():
    session_id = str(uuid.uuid4())
    redis_client.setex(f'session:{session_id}', SESSION_TTL, json.dumps([]))
    return session_id

def load_history(session_id):
    data = redis_client.get(f'session:{session_id}')
    if data:
        try:
            return json.loads(data)
        except Exception:
            return []
    return []

def save_history(session_id, history):
    redis_client.setex(f'session:{session_id}', SESSION_TTL, json.dumps(history))

def delete_session(session_id):
    redis_client.delete(f'session:{session_id}')
