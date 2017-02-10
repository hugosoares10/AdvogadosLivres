<?php
    function getContentFromURL($URL){
        //return file_get_contents($URL);
        return curl_get_contents($URL);

        $handle = fopen($URL, 'r');
        $buffer = '';
        if ($handle) {
            while (!feof($handle)) {
                $buffer .= fgets($handle, 5000);
            }
            fclose($handle);
        }

        return $buffer;
    }
    function curl_get_contents($url){
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }
    function postVoiceData($URL_TO_SEND, $voiceData){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $URL_TO_SEND);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $voiceData.';type=audio/ogg');
        //curl_setopt($curl, CURLOPT_POSTFIELDS, array("filedata" => "@$data", "filename" => 'teste.ogg'));
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
    function getTelegramData($URL){
        $retornoJSON = getContentFromURL($URL);

        $retorno = json_decode($retornoJSON);

        if($retorno->ok === true){
            $conteudo = $retorno->result;
        }else{
            var_dump($retorno);
            exit;
        }

        return $conteudo;
    }
    function prepareTelegramFile($file){
        $URL = TELEGRAM_API_URL . 'getFile?file_id=' . $file->file_id;

        $conteudo = getTelegramData($URL);

        return $conteudo;
    }
    function getTelegramFileURL($filePrepared){
        $URL = TELEGRAM_API_URL_BASE . 'file/bot' . TELEGRAM_API_TOKEN . '/' . $filePrepared->file_path;
        
        return $URL;
    }
    function sendTelegramChatAction($message, $action){
        $URL = TELEGRAM_API_URL . 'sendChatAction?chat_id=' . $message->chat->id . '&action=' . $action;

        $conteudo = getTelegramData($URL);

        return $conteudo;
    }
    function sendTelegramMessage($message, $text){
        $URL = TELEGRAM_API_URL . 'sendMessage?chat_id=' . $message->chat->id . '&text=' . urlencode($text) . '&reply_to_message_id=' . $message->message_id;

        $conteudo = getTelegramData($URL);

        return $conteudo;
    }
    function sendTelegramVoice($message, $voiceData){
        $URL = TELEGRAM_API_URL . 'sendVoice?chat_id=' . $message->chat->id . '&voice=' . urlencode($URL_VOICE_FILE) . '&reply_to_message_id=' . $message->message_id;
        $URL = TELEGRAM_API_URL . 'sendVoice?chat_id=' . $message->chat->id . '&reply_to_message_id=' . $message->message_id;

        //$conteudo = getTelegramData($URL);
        $retorno = postVoiceData($URL, $voiceData);

        return $retorno;
    }
    function downloadFileToServer($URL_FROM, $PATH_TO, $originalFileName = true){
        $FILE_NAME = strrchr($URL_FROM, '/');
        $EXTENSION = strrchr($FILE_NAME, '.');

        if($originalFileName){
            $ONLY_NAME = substr($FILE_NAME, 0, strrpos($FILE_NAME, '.'));
        }else{
            $ONLY_NAME = '/' . date('Ymd-His');
        }

        $FULL_PATH = $PATH_TO . $ONLY_NAME . $EXTENSION;
        $i = 0;
        while(file_exists($FULL_PATH)){
            $i++;
            $FULL_PATH = $PATH_TO . $ONLY_NAME . '_' . $i . $EXTENSION;
        }

        $qtdBytes = file_put_contents($FULL_PATH, fopen($URL_FROM, 'r'));

        if($qtdBytes === false){
            return false;
        }else{
            $FILE_NAME_SAVED = strrchr($FULL_PATH, '/');
            return $FILE_NAME_SAVED;
        }
    }
    function consultarWatsonSTT($URL_AUDIO){
        $URL = WATSON_API_URL_STT . $URL_AUDIO;
        
        $retornoJSON = getContentFromURL($URL);

        $retorno = json_decode($retornoJSON);
        
        if(!empty($retorno)){
            $corpo = $retorno[0];
            $alternatives = $corpo->alternatives[0];
            $conteudo = $alternatives->transcript;
        }else{
            var_dump($retorno);
            exit;
        }

        return $conteudo;
    }
    function consultarWatsonCON($text){
        $URL = WATSON_API_URL_CON . urlencode($text);
        
        $retornoJSON = getContentFromURL($URL);

        $retorno = json_decode($retornoJSON);

        if(empty($retorno->log_messages)){
            $conteudo = $retorno->text[0];
        }else{
            var_dump($retorno);
            exit;
        }

        return $conteudo;
    }
    function consultarWatsonTTS($text){
        $URL = WATSON_API_URL_TTS . urlencode($text);

        $retornoRAW = getContentFromURL($URL);

        return $retornoRAW;
    }
    function processarChatBot($message){
        if(isset($message->text)){
            $text = $message->text;
            if($text == '/start'){
                $textoEnviar = 'Bem-vindo ao Chatbot do Me Defenda. Para interagir, envie uma mensagem de voz/áudio.';
                sendTelegramMessage($message, $textoEnviar);
                var_dump($textoEnviar);
            }else{
                $textoEnviar = 'Estamos aprimorando o serviço, no momento não foi possível processar o seu comando.';
                sendTelegramMessage($message, $textoEnviar);
                var_dump($textoEnviar);
            }
        }else if(isset($message->voice)){
            //altera o status do ChatBot para o usuário ter um retorno do que está acontecendo
            //$retorno = sendTelegramChatAction($message, 'upload_audio');
            $retorno = sendTelegramChatAction($message, 'typing');
            var_dump($retorno);

            $voice = $message->voice;

            $voicePrepared = prepareTelegramFile($voice);
            
            $URL_AUDIO_TELEGRAM = getTelegramFileURL($voicePrepared);

            $retornoWatsonSTT = consultarWatsonSTT($URL_AUDIO_TELEGRAM);
            if(empty($retornoWatsonSTT)){
                $textoEnviar = 'Não foi possível processar o áudio.';
                sendTelegramMessage($message, $textoEnviar);
                var_dump($textoEnviar);
            }else{
                $textoEnviar = 'Entendemos: "' . $retornoWatsonSTT . '". Contextualizando a frase...';
                sendTelegramMessage($message, $textoEnviar);
                var_dump($textoEnviar);

                $retornoWatsonCON = consultarWatsonCON($retornoWatsonSTT);

                $textoEnviar = 'A resposta é: "' . $retornoWatsonCON . '".';
                sendTelegramMessage($message, $textoEnviar);
                var_dump($textoEnviar);

                //$retornoWatsonTTS = consultarWatsonTTS($retornoWatsonCON);
                //$retorno = sendTelegramVoice($message, $retornoWatsonTTS);
                //var_dump($retorno);
            }
        }else{
            $retorno = sendTelegramMessage($message, 'Desculpe, no momento só é aceito mensagem de voz.');
            var_dump($retorno);
        }
    }