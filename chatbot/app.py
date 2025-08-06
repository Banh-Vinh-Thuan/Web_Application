# app.py - PHIÊN BẢN ĐÃ SỬA LỖI
import torch
from flask import Flask, request, jsonify
from flask_cors import CORS
from transformers import AutoTokenizer, AutoModelForCausalLM
import json
import logging
import re
import os
from datetime import datetime

# --- Logging Configuration ---
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# --- Flask App Configuration ---
app = Flask(__name__)
CORS(app) # Allows requests from any origin

# --- Global Variables & Model Loading ---
MODEL_PATH = "viettransit_model"
TOKENIZER_PATH = "microsoft/phi-2" # Phải khớp với tokenizer dùng khi train
device = "cuda" if torch.cuda.is_available() else "cpu"
model = None
tokenizer = None
knowledge_base = ""
conversation_history = {}

# --- Data Loading ---
def load_data():
    """Load and prepare knowledge base from JSON files with robust error handling."""
    global knowledge_base
    
    default_tours = [{"title": "Default Tour", "short_description": "An exciting travel package."}]
    default_hotels = [{"name": "Default Hotel", "address": "A comfortable place to stay."}]

    tours_data = default_tours
    hotels_data = default_hotels

    try:
        if os.path.exists('tour.json'):
            with open('tour.json', 'r', encoding='utf-8') as f:
                loaded_tour_data = json.load(f)
                if isinstance(loaded_tour_data, dict):
                    all_tours = []
                    for location, tour_list in loaded_tour_data.items():
                        if isinstance(tour_list, list):
                            all_tours.extend(tour_list)
                    if all_tours:
                        tours_data = all_tours
                        logger.info(f"Successfully loaded {len(tours_data)} tours from tour.json")
                    else:
                        logger.warning("tour.json was loaded but contained no tour lists. Using default data.")
                else:
                    logger.warning("tour.json is not a dictionary. Using default tour data.")
        else:
            logger.warning("tour.json not found, using default tour data.")
    except Exception as e:
        logger.error(f"Error processing tour.json: {e}. Using default tour data.")

    try:
        if os.path.exists('hoteladdress.json'):
            with open('hoteladdress.json', 'r', encoding='utf-8') as f:
                loaded_hotel_data = json.load(f)
                if isinstance(loaded_hotel_data, dict):
                    all_hotels = []
                    for location, hotel_list in loaded_hotel_data.items():
                        if isinstance(hotel_list, list):
                            all_hotels.extend(hotel_list)
                    if all_hotels:
                        hotels_data = all_hotels
                        logger.info(f"Successfully loaded {len(hotels_data)} hotels from hoteladdress.json")
                    else:
                        logger.warning("hoteladdress.json was loaded but contained no hotel lists. Using default data.")
                else:
                    logger.warning("hoteladdress.json is not a dictionary. Using default hotel data.")
        else:
            logger.warning("hoteladdress.json not found, using default hotel data.")
    except Exception as e:
        logger.error(f"Error processing hoteladdress.json: {e}. Using default hotel data.")

    tours_summary = "AVAILABLE TOURS:\n"
    for tour in tours_data:
        name = tour.get('title', 'Unknown Tour')
        desc = tour.get('short_description', 'No description available.')
        tours_summary += f"- {name}: {desc}\n"
    
    hotels_summary = "\nAVAILABLE HOTELS:\n"
    for hotel in hotels_data:
        name = hotel.get('name', 'Unknown Hotel')
        address = hotel.get('address', 'No address available.')
        hotels_summary += f"- {name} is located in {address}.\n"
    
    knowledge_base = tours_summary + hotels_summary
    logger.info("Knowledge base created successfully.")

# --- Model Loading ---
def load_model_and_tokenizer():
    global model, tokenizer
    logger.info(f"Loading model from {MODEL_PATH} on device: {device}")
    try:
        if not os.path.isdir(MODEL_PATH):
            logger.error(f"Model directory not found at {MODEL_PATH}. Please run train.py first.")
            raise FileNotFoundError(f"Model directory not found: {MODEL_PATH}")
            
        model = AutoModelForCausalLM.from_pretrained(
            MODEL_PATH,
            device_map="auto",
            torch_dtype=torch.float16,
        )
        tokenizer = AutoTokenizer.from_pretrained(TOKENIZER_PATH)
        
        if tokenizer.pad_token is None:
            tokenizer.pad_token = tokenizer.eos_token
        
        model.eval()
        logger.info("Model and tokenizer loaded successfully")
    except Exception as e:
        logger.error(f"Error loading model: {e}")
        raise e

# --- Text Processing Functions ---
def clean_response(response_text):
    response_text = re.sub(r'<\|.*?\|>', '', response_text)
    response_text = response_text.strip()
    # Lấy phần trả lời của Assistant sau cùng
    parts = response_text.split("Assistant:")
    if len(parts) > 1:
        return parts[-1].strip()
    return response_text

def generate_fallback_response(user_query):
    query_lower = user_query.lower()
    if any(word in query_lower for word in ['tour', 'trip', 'travel', 'visit']):
        return "I can help you find the perfect tour package! We have many options across Vietnam. Which destination interests you most?"
    elif any(word in query_lower for word in ['hotel', 'accommodation', 'stay', 'room']):
        return "We have excellent hotels available! Where would you like to stay?"
    return "I can assist with tours and hotels in Vietnam. How can I help you?"

# --- API Endpoints ---
@app.route('/api/chat', methods=['POST'])
def chat():
    if model is None or tokenizer is None:
        return jsonify({'error': 'Model is not loaded'}), 503

    try:
        data = request.get_json()
        user_query = data.get('query', '').strip()
        session_id = data.get('session_id', 'default')
        if not user_query:
            return jsonify({'error': 'Query cannot be empty'}), 400
        
        if session_id not in conversation_history:
            conversation_history[session_id] = ""
        
        history = conversation_history[session_id]
        prompt = f"<s>[INST] {user_query} [/INST]" # Sử dụng prompt template giống như khi train
        
        inputs = tokenizer(prompt, return_tensors="pt").to(device)
        
        with torch.no_grad():
            outputs = model.generate(
                **inputs, max_new_tokens=150, do_sample=True, temperature=0.7, top_k=50,
                pad_token_id=tokenizer.eos_token_id
            )
        
        full_response = tokenizer.decode(outputs[0], skip_special_tokens=True)
        bot_response = clean_response(full_response)
        
        if not bot_response or len(bot_response) < 10:
            bot_response = generate_fallback_response(user_query)
            
        conversation_history[session_id] += f"User: {user_query}\nAssistant: {bot_response}\n"
        
        return jsonify({'reply': bot_response})
        
    except Exception as e:
        logger.error(f"Error in chat endpoint: {e}", exc_info=True)
        return jsonify({'error': 'An error occurred on the server.'}), 500

@app.route('/api/health', methods=['GET'])
def health_check():
    return jsonify({'status': 'healthy', 'model_loaded': model is not None})

def initialize_app():
    logger.info("Initializing VietTransit Chatbot...")
    load_data()
    load_model_and_tokenizer()
    logger.info("Application initialized successfully!")

if __name__ == '__main__':
    try:
        initialize_app()
        # Chạy trên port 3000 để khớp với frontend
        app.run(host='0.0.0.0', port=3000, debug=False) 
    except Exception as e:
        logger.error(f"Failed to start server: {e}")