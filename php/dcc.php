<?php

use exert\DirectClient;

require './vendor/autoload.php';

$dc = new DirectClient('dddd');

$dc->consume(function ($message) {
    echo "\n--------\n";
    echo $message->body;
    echo "\n--------\n";

    $message->ack();

    if ($message->body === 'quit') {
        $message->getChannel()->basic_cancel($message->getConsumerTag());
    }
});
