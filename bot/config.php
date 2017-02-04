<?php
    require_once 'functions.php';

    define('SERVER_URL_BASE', 'https://advogadoslivres.mybluemix.net/');
    define('SERVER_PATH_BASE', __DIR__);

    define('TELEGRAM_API_TOKEN', '281211374:AAGK40tLwgPBBq0hViKAoCjDUcbsdGGgW3Y');
    define('TELEGRAM_API_URL_BASE', 'https://api.telegram.org/');
    define('TELEGRAM_API_URL', TELEGRAM_API_URL_BASE . 'bot' . TELEGRAM_API_TOKEN . '/');
    
    define('WATSON_API_URL_STT', 'http://al-node-red.mybluemix.net/stt?q=');
    define('WATSON_API_URL_CON', 'http://al-node-red.mybluemix.net/con?q=');
    define('WATSON_API_URL_TTS', 'http://al-node-red.mybluemix.net/tts?q=');

    date_default_timezone_set('America/Sao_Paulo');