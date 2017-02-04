<?php
    require_once 'config.php';

    $URL = TELEGRAM_API_URL . 'getUpdates';

    $conteudo = getTelegramData($URL);

    echo '<pre>';

    //ordenar em ordem da mensagem mais recente (decrescente)
    function cmp($a, $b){
        return strcmp($b->update_id, $a->update_id);
    }
    usort($conteudo, "cmp");

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
            $textoEnviar = 'Processando o áudio...';
            sendTelegramMessage($mensagem->chat, $textoEnviar);
            var_dump($textoEnviar);

            $voice = $mensagem->voice;

            $voicePrepared = prepareTelegramFile($voice);
            
            $URL_AUDIO_TELEGRAM = getTelegramFileURL($voicePrepared);

            $retornoWatsonSTT = consultarWatsonSTT($URL_AUDIO_TELEGRAM);
            if(empty($retornoWatsonSTT)){
                $textoEnviar = 'Não foi possível processar o áudio.';
                sendTelegramMessage($mensagem->chat, $textoEnviar);
                var_dump($textoEnviar);
            }else{
                $textoEnviar = 'Entendemos: "' . $retornoWatsonSTT . '". Contextualizando a frase...';
                sendTelegramMessage($mensagem->chat, $textoEnviar);
                var_dump($textoEnviar);

                $retornoWatsonCON = consultarWatsonCON($retornoWatsonSTT);

                $textoEnviar = 'A resposta é: "' . $retornoWatsonCON . '".';
                sendTelegramMessage($mensagem->chat, $textoEnviar);
                var_dump($textoEnviar);
            }
            echo 'URL áudio: ' . $URL_AUDIO_TELEGRAM . "\n";
            exit;
        }else{
            echo 'Não é mensagem de voz'."\n";
        }
    }