<?php

namespace exert;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

/**
 * 排他队列
 * 
 * 自产自销。
 * 一般用于处理临时任务时把有限的任务存入再消耗。
 * 链接断开时会销毁队列，可同事销毁 exchange 。
 * 
 */
class ExclusiveQueue
{
    /**
     * 启用一个排外队列。
     *
     * @param Callable $publish
     * @param string $exchange
     * @param Callable $consume
     * @return void
     */
    public static function apply($publish, $exchange, $consume)
    {
        try {
            $consumerTag = 'consumer' . getmypid();
            $connection = new AMQPStreamConnection(
                'localhost',
                5672,
                'admin',
                'admin',
                '/'
            );
            $channel = $connection->channel();
            [$queue,,] = $channel->queue_declare(
                '',
                false,
                false,
                true,
                true
            );
            $channel->exchange_declare(
                $exchange,
                AMQPExchangeType::FANOUT,
                false,
                false,
                true
            );
            $channel->queue_bind($queue, $exchange);

            $publish($channel, $queue, $exchange);

            // 绑定消费程序。
            $channel->basic_consume($queue, $consumerTag, false, false, true, false, $consume);
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
