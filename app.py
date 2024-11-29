from flask import Flask, request, jsonify
import pickle
import re
import string
import nltk
from nltk.tokenize import word_tokenize
from nltk.corpus import stopwords
import pandas as pd
from nltk.stem import PorterStemmer
import logging
from pathlib import Path


def ensure_nltk_downloads():
    """Ensure all required NLTK data is downloaded"""
    try:
        required_packages = ['punkt', 'stopwords', 'punkt_tab']
        for package in required_packages:
            try:
                nltk.data.find(f'tokenizers/{package}' if package == 'punkt' else f'corpora/{package}')
                print(f"Found {package} data")
            except LookupError:
                print(f"Downloading {package}...")
                nltk.download(package, quiet=True)
                print(f"Successfully downloaded {package}")
    except Exception as e:
        print(f"Error downloading NLTK data: {str(e)}")
        raise

ensure_nltk_downloads()

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class BiasDetector:
    def __init__(self):
        ensure_nltk_downloads()
        self.load_models()
    
    def load_models(self):
        """Load all required model files"""
        try:
            pickle_dir = Path('./pickles')
            
            # Load selector
            with open(pickle_dir / 'selector.pkl', 'rb') as f:
                self.selector = pickle.load(f)

            # Load SVD
            with open(pickle_dir / 'svd.pkl', 'rb') as f:
                self.svd = pickle.load(f)
            
            # Load vectorizer
            with open(pickle_dir / 'vectorizer.pkl', 'rb') as f:
                self.vectorizer = pickle.load(f)
            
            # Load model
            with open(pickle_dir / 'ANN.pkl', 'rb') as f:
                self.model = pickle.load(f)
                
            logger.info("All models loaded successfully")
        except Exception as e:
            logger.error(f"Error loading models: {str(e)}")
            raise
    
    # Preprocessing Functions
    def clean_text(self, text):
        text = str(text).lower()
        text = re.sub('\[.*?\]', '', text)
        text = re.sub('[%s]' % re.escape(string.punctuation), '', text)
        text = re.sub('\w*\d\w*', '', text)
        text = re.sub(r'[^\x00-\x7F]+', '', text)
        text = re.sub('[’‘’“”…]', '', text)
        text = re.sub('\n', '', text)
        text = re.sub(r'[^\w\s]', '', text)
        text = re.sub(r'\d+', '', text)
        return text

    def remove_stopwords(self, text):
        """Remove stopwords from text"""
        stop_words = set(stopwords.words('english'))
        additional_stop_words = ['us', 'today', 'amp', 'day', 'time', 'one', 'rt', 
                               'im', 'get', 'would', 'week', 'year', 'years', 
                               "said", "told", "news", "like", "also", "could", 
                               "many", "two", "first", "last", 'say']
        tokens = word_tokenize(text)
        cleaned_tokens = [word for word in tokens if word.isalnum() 
                         and word not in stop_words 
                         and word not in additional_stop_words]
        return ' '.join(cleaned_tokens)

    def tokenize_and_stem(self, text):
        """Tokenize and stem text"""
        stemmer = PorterStemmer()
        tokens = word_tokenize(text.lower())
        stemmed_tokens = [stemmer.stem(token) for token in tokens]
        return ' '.join(stemmed_tokens)

    def feature_selection_svd(self, dtm):
        """Apply feature selection and SVD"""
        selected_features = self.selector.transform(dtm)
        reduced_features = self.svd.transform(selected_features)
        return reduced_features

    def preprocessing(self, text):
        """Complete preprocessing pipeline"""
        cleaned_text = self.clean_text(text)
        text_no_stopwords = self.remove_stopwords(cleaned_text)
        stemmed_text = self.tokenize_and_stem(text_no_stopwords)
        dtm = self.vectorizer.transform([stemmed_text])
        final_features = self.feature_selection_svd(dtm)
        return final_features

    def predict(self, text):
        """Make prediction on preprocessed text"""
        try:
            preprocessed_features = self.preprocessing(text)
            prediction = self.model.predict(preprocessed_features)
            return prediction.tolist()
        except Exception as e:
            logger.error(f"Error in prediction: {str(e)}")
            raise

app = Flask(__name__)
detector = BiasDetector()

@app.route('/predict', methods=['POST'])
def predict():
    logger.info("Received request")
    try:
        data = request.json
        if not data or 'text' not in data:
            return jsonify({'error': 'No text provided'}), 400
            
        input_text = data['text']
        if not input_text or not isinstance(input_text, str):
            return jsonify({'error': 'Invalid text format'}), 400
            
        prediction = detector.predict(input_text) # Preprocesses and predicts
        logger.info(f"Prediction: {prediction}")
        return jsonify({
            'prediction': prediction,
            'status': 'success'
        })

    except Exception as e:
        logger.error(f"Error processing request: {str(e)}")
        return jsonify({
            'error': str(e),
            'status': 'error'
        }), 500

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000)