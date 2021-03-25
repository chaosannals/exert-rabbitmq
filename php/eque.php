<?php

use exert\ExclusiveQueue;
use PhpAmqpLib\Message\AMQPMessage;

require './vendor/autoload.php';

$exchange = 'eque';
ExclusiveQueue::apply(function ($channel, $queue) use ($exchange) {
    // 生产信息。
    for ($i = 0; $i <= 100; ++$i) {
        $messageBody = $i == 100 ? 'quit' : "I'm a exclusive queue message ($i)";
        $message = new AMQPMessage(
            $messageBody,
            [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]
        );
        $channel->basic_publish($message, $exchange);
    }
}, $exchange, function ($message) {
    echo "\n--------\n";
    echo $message->body;
    echo "\n--------\n";

    $message->ack();

    if ($message->body === 'quit') {
        $message->getChannel()->basic_cancel($message->getConsumerTag());
    }
});
