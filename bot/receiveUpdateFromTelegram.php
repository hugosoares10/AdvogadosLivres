<?php
    require_once 'config.php';

    $jsonReceived = file_get_contents("php://input");
    $jsonDecode = json_decode($jsonReceived);

    $logTelegramMessage = new stdClass();
    $logTelegramMessage->chat = new stdClass();
    $logTelegramMessage->chat->id = 106723363;
    if(empty($jsonDecode)){
        sendTelegramMessage($logTelegramMessage, 'Nenhuma informação recebida no JSON');
    }else{
        sendTelegramMessage($logTelegramMessage, $jsonReceived);
    }

    if(isset($jsonDecode->message)){
        $message = $jsonDecode->message;
        if(isset($message->text)){
            $text = $message->voice;
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
            $textoEnviar = 'Processando o áudio...';
            sendTelegramMessage($message, $textoEnviar);
            var_dump($textoEnviar);

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

                //$textoEnviar = 'A resposta é: "' . $retornoWatsonCON . '".';
                //sendTelegramMessage($message, $textoEnviar);
                //var_dump($textoEnviar);

                $URL_VOICE_ANSWER = consultarWatsonTTS($retornoWatsonCON);
                sendTelegramVoice($message, $URL_VOICE_ANSWER);
                var_dump($URL_VOICE_ANSWER);
            }
        }else{
            sendTelegramMessage($message, 'Desculpe, no momento só é aceito mensagem de voz.');
        }
    }