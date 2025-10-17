from flask import Flask, request, jsonify
from waitress import serve
from flask_cors import CORS   # <-- NEW
import torch
from cachetools import LRUCache
from cachetools.keys import hashkey

from pipeline import process_question_pipeline

torch.set_float32_matmul_precision('high')
torch.backends.cuda.matmul.allow_tf32 = True
torch.backends.cudnn.allow_tf32 = True

app = Flask(__name__)
CORS(  # <-- NEW: CORS global (tu peux restreindre les origines si besoin)
    app,
    resources={r"/ask": {"origins": "*"}},
    supports_credentials=False
)
_pipeline_cache = LRUCache(maxsize=200)

def get_cached_pipeline_result(question):
    key = hashkey(question.strip().lower())
    if key in _pipeline_cache:
        return _pipeline_cache[key]
    result = process_question_pipeline(question=question)
    _pipeline_cache[key] = result
    return result

@app.route('/', methods=['GET'])
def health_check():
    return 'NLP2SQL Flask API is running!', 200

@app.route('/ask', methods=['POST', 'OPTIONS'])  # <-- OPTIONS pour le préflight
def ask():
    if request.method == 'OPTIONS':
        return ('', 204)

    try:
        data = request.get_json(force=True)
        user_question = data.get("question")

        if not user_question:
            return jsonify({"error": "Merci de fournir un champ 'question'."}), 400

        result = get_cached_pipeline_result(user_question)

        rows = result.get("final_result", {}).get("rows", [])
        # Sécuriser le format: liste de listes
        if rows is None:
            rows = []
        elif isinstance(rows, dict):
            rows = [list(rows.values())]
        elif rows and isinstance(rows[0], dict):
            rows = [list(r.values()) for r in rows]

        response = {
            # clé attendue + alias compatibles front
            "type": result.get("mode", "sql"),
            "type_": result.get("mode", "sql"),                  # alias
            "dataset": rows,
            "raison": result.get("llama_reason", ""),
            "text": result.get("user_friendly_answer", ""),
            "reftable": result.get("sql_validated_post_exec", ""),
            "tables": result.get("sql_validated_post_exec", "")  # alias
        }
        return jsonify(response), 200

    except Exception as e:
        print(f"[ERROR] /ask failed: {e}")
        return jsonify({"error": f"Erreur interne : {str(e)}"}), 500

if __name__ == '__main__':
    print('Initializing application components...')
    # Si besoin d’éviter le parallélisme GPU, force threads=1
    serve(app, host='0.0.0.0', port=3000, threads=4)
