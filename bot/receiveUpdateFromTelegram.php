<?php
    require_once 'config.php';

    $jsonReceived = file_get_contents("php://input");
    $jsonDecode = json_decode($jsonReceived);

    $logTelegramMessage = new stdClass();
    $logTelegramMessage->message_id = 211;
    $logTelegramMessage->chat = new stdClass();
    $logTelegramMessage->chat->id = 106723363;
    if(empty($jsonDecode)){
        sendTelegramMessage($logTelegramMessage, 'Nenhuma informação recebida no JSON');
    }else{
        sendTelegramMessage($logTelegramMessage, $jsonReceived);
    }

    if(isset($jsonDecode->message)){
        $message = $jsonDecode->message;
        processarChatBot($message);
    }