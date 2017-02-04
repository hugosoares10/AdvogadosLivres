<?php
    require_once 'config.php';

    $jsonReceived = file_get_contents("php://input");
    $jsonDecode = json_decode($jsonReceived);

    $logTelegramChat = new stdClass();
    $logTelegramChat->id = 106723363;
    if(empty($jsonDecode)){
        sendTelegramMessage($logTelegramChat, 'Nenhuma informação recebida no JSON');
    }else{
        sendTelegramMessage($logTelegramChat, $jsonReceived);
    }

    if(isset($jsonDecode->message)){
        if(isset($message->voice)){
            $textoEnviar = 'Processando o áudio...';
            sendTelegramMessage($message->chat, $textoEnviar);
            var_dump($textoEnviar);

            $voice = $message->voice;

            $voicePrepared = prepareTelegramFile($voice);
            
            $URL_AUDIO_TELEGRAM = getTelegramFileURL($voicePrepared);

            $retornoWatsonSTT = consultarWatsonSTT($URL_AUDIO_TELEGRAM);
            if(empty($retornoWatsonSTT)){
                $textoEnviar = 'Não foi possível processar o áudio.';
                sendTelegramMessage($message->chat, $textoEnviar);
                var_dump($textoEnviar);
            }else{
                $textoEnviar = 'Entendemos: "' . $retornoWatsonSTT . '". Contextualizando a frase...';
                sendTelegramMessage($message->chat, $textoEnviar);
                var_dump($textoEnviar);

                $retornoWatsonCON = consultarWatsonCON($retornoWatsonSTT);

                $textoEnviar = 'A resposta é: "' . $retornoWatsonCON . '".';
                sendTelegramMessage($message->chat, $textoEnviar);
                var_dump($textoEnviar);
            }
        }else{
            sendTelegramMessage($message->chat, 'Desculpe, no momento só é aceito mensagem de voz.');
        }
    }