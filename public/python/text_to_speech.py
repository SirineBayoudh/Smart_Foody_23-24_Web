import os
import tempfile
from gtts import gTTS
import pygame
from flask import send_file

def text_to_speech(text, lang='en'):
    """
    Convertit le texte en discours audio et le lit.

    Args:
        text (str): Le texte à convertir.
        lang (str, optional): La langue du texte (par défaut est 'en' pour l'anglais).

    """
    # Utiliser gTTS pour convertir le texte en discours audio
    tts = gTTS(text=text, lang=lang)

    # Utiliser un fichier temporaire pour stocker le fichier audio
    with tempfile.NamedTemporaryFile(delete=False) as temp_file:
        audio_path = temp_file.name
        tts.save(audio_path)

    # Initialiser le module pygame
    pygame.init()

    try:
        # Charger le fichier audio avec pygame
        pygame.mixer.music.load(audio_path)

        # Jouer le fichier audio
        pygame.mixer.music.play()

        # Attendre jusqu'à la fin de la lecture
        while pygame.mixer.music.get_busy():
            pygame.time.Clock().tick(10)
    finally:
        # Supprimer le fichier audio temporaire après la lecture
        pygame.mixer.music.stop()
        pygame.quit()
        os.unlink(audio_path)
