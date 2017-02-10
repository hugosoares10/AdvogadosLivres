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
        $message = $item->message;

        var_dump($message);

        if(isset($message->voice)){
            processarChatBot($message);
            exit;
        }else{
            echo 'Não é mensagem de voz'."\n";
        }
    }