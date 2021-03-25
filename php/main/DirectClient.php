<?php

namespace exert;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 直接往队列里面插入。
 * 
 */
class DirectClient
{
    private $queue;
    private $exchange;

    public function __construct($queue, $exchange = 'direct-client')
    {
        $this->queue = $queue;
        $this->exchange = $exchange;
    }

    /**
     * 发布信息
     *
     * @param string $messageBody
     * @return void
     */
    public function publish($messageBody)
    {
        try {
            $connection = new AMQPStreamConnection(
                'localhost',
                5672,
                'admin',
                'admin',
                '/'
            );
            $channel = $connection->channel();
            $channel->queue_declare($this->queue, false, true, false, false);
            $channel->exchange_declare($this->exchange, AMQPExchangeType::DIRECT, false, true, false);
            $channel->queue_bind($this->queue, $this->exchange);
            $message = new AMQPMessage(
                $messageBody,
                [
                    'content_type' => 'text/plain',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]
            );
            $channel->basic_publish($message, $this->exchange);
        } finally {
            $channel->close();
            $connection->close();
        }
    }

    public function consume($callback)
    {
        try {
            $connection = new AMQPStreamConnection(
                'localhost',
                5672,
                'admin',
                'admin',
                '/'
            );
            $channel = $connection->channel();
            $consumerTag = 'consumer' . getmypid();
            $channel->queue_declare($this->queue, false, true, false, false);
            $channel->exchange_declare($this->exchange, AMQPExchangeType::DIRECT, false, true, false);
            $channel->queue_bind($this->queue, $this->exchange);
            $channel->basic_consume($this->queue, $consumerTag, false, false, true, false, $callback);
            // 消费
            while ($channel->is_consuming()) {
                $channel->wait();
            }
        } finally {
            $channel->close();
            $connection->close();
            echo 'shadown';
        }
    }
}
