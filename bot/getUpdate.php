<?php
    require_once 'config.php';

    $URL = TELEGRAM_API_URL . 'getUpdates';

    $conteudo = getData($URL);

    echo '<pre>';

    foreach($conteudo as $item){
        $mensagem = $item->message;

        //echo 'Message ID: ' . $mensagem->message_id . '<br />';

        /*echo 'De: ';
        foreach($mensagem->from as $from){
            echo $from . ' ';
        }
        echo '<br />';*/

        /*echo 'Chat: ';
        foreach($mensagem->chat as $chat){
            echo $chat . ' ';
        }
        echo '<br />';*/

        if(isset($mensagem->voice)){
            $voice = $mensagem->voice;

            $voicePrepared = prepareAudioFile($voice);
            
            $URL_AUDIO_TELEGRAM = getURLAudioFile($voicePrepared);

            $PATH_AUDIO_SERVER = getPathNameWithFileToStore($voicePrepared->file_path);

            if(downloadAudioToServer($URL_AUDIO_TELEGRAM, $PATH_AUDIO_SERVER)){

                $URL_AUDIO_SERVER = SERVER_URL_BASE . 'bot' . PATH_AUDIO_SERVER;

                sendMessage($mensagem->chat, $URL_AUDIO_SERVER);

                echo 'URL audio: ' . $URL_AUDIO_SERVER . "\n";
            }else{
                echo 'Não foi possível baixar o áudio para o servidor'."\n";
            }
        }else{
            echo 'Não é mensagem de texto'."\n";
        }
    }

    function getData($URL){
        $retornoJSON = file_get_contents($URL);

        $retorno = json_decode($retornoJSON);

        if($retorno->ok === true){
            $conteudo = $retorno->result;
        }else{
            var_dump($retorno);
            exit;
        }

        return $conteudo;
    }
    function prepareAudioFile($voice){
        $URL = TELEGRAM_API_URL . 'getFile?file_id=' . $voice->file_id;

        $conteudo = getData($URL);

        return $conteudo;
    }
    function getURLAudioFile($voicePrepared){
        $URL = TELEGRAM_API_URL_BASE . 'file/bot' . TELEGRAM_API_TOKEN . '/' . $voicePrepared->file_path . '?file_id=' . $voicePrepared->file_id;
        
        return $URL;
    }
    function sendMessage($chat, $text){
        $URL = TELEGRAM_API_URL . 'sendMessage?chat_id=' . $chat->id . '&text=' . $text;

        $conteudo = getData($URL);

        return $conteudo;
    }
    function downloadAudioToServer($URL_FROM, $PATH_TO){
        return file_put_contents($PATH_TO, fopen($URL_FROM, 'r'));
    }
    function getPathNameWithFileToStore($FILE_PATH){
        $PATH_DIR = __DIR__ . '/file/';
        
        if(!is_dir($PATH_DIR)){
            mkdir($PATH_DIR);
        }

        $NAME_FILE = date('Ymd-His');
        $EXTENSAO = strrchr($FILE_PATH, '.');
        $PATH_FULL = $PATH_DIR . $NAME_FILE . $EXTENSAO;
        $i = 1;
        while(file_exists($PATH_FULL)){
            $PATH_FULL = $PATH_DIR . $NAME_FILE . '_' . $i . $EXTENSAO;
            $i++;
        }

        return $PATH_FULL;
    }