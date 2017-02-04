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
    function sendTelegramMessage($chat, $text){
        $URL = TELEGRAM_API_URL . 'sendMessage?chat_id=' . $chat->id . '&text=' . urlencode($text);

        $conteudo = getTelegramData($URL);

        return $conteudo;
    }
    function downloadFileToServer($URL_FROM, $PATH_TO, $originalFileName = true){
        if($originalFileName){
            $FILE_NAME = strrchr($URL_FROM, '/');
        }else{
            $FILE_NAME = date('Ymd-His');
        }
        $EXTENSION = strrchr($FILE_NAME, '.');

        $FULL_PATH = $PATH_TO . $FILE_NAME . $EXTENSION;
        $i = 0;
        while(file_exists($FULL_PATH)){
            $i++;
            $FULL_PATH = $PATH_TO . $FILE_NAME . '_' . $i . $EXTENSION;
        }

        $qtdBytes = file_put_contents($FULL_PATH, fopen($URL_FROM, 'r'));

        if($qtdBytes === false){
            return false;
        }else{
            return strrchr($FULL_PATH, '/');
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