from flask import Flask, request, jsonify
from text_to_speech import text_to_speech

app = Flask(__name__)

# Ajouter un en-tête pour autoriser les requêtes CORS
@app.after_request
def add_cors_headers(response):
    response.headers['Access-Control-Allow-Origin'] = '*'
    response.headers['Access-Control-Allow-Headers'] = 'Content-Type'
    response.headers['Access-Control-Allow-Methods'] = 'POST'
    return response

@app.route('/convert', methods=['POST'])
def convert_text_to_speech():
    # Récupérer le texte de la requête POST
    text = request.json.get('text')

    # Appeler la fonction de conversion de texte en discours audio
    audio_path = text_to_speech(text)

    # Retourner le chemin d'accès vers le fichier audio
    return jsonify({'audio_path': audio_path})

if __name__ == '__main__':
    app.run(debug=True)
