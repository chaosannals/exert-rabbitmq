import pika
import json

credentials = pika.PlainCredentials('admin', 'admin')

connection = pika.BlockingConnection(
    pika.ConnectionParameters(
        host='127.0.0.1',
        port=5672,
        virtual_host='/',
        credentials=credentials
    )
)
channel = connection.channel()
channel.exchange_declare(
    exchange = 'exert.fanout',
    durable = True, # 持久化
    # fanout 发送给所有绑定的队列
    # direct 队列和 exchange 必须完全匹配
    # topic 队列和 exchange 必须都匹配，但 key 是模糊语句匹配
    # headers 根据消息 headers 和绑定时的 headers 完全匹配。
    exchange_type='fanout',
)
result = channel.queue_declare(
    queue='python-exert-1',
    durable=True, # 持久化
)
channel.queue_bind(exchange = 'exert.fanout',queue = result.method.queue,routing_key='no')
result = channel.queue_declare(
    queue='python-exert-2',
    durable=True, # 持久化
)
channel.queue_bind(exchange = 'exert.fanout',queue = result.method.queue,routing_key='no')
for i in range(100):
    message = json.dumps({'no': f"1000{i}"})
    # 向队列插入数值 routing_key是队列名
    channel.basic_publish(
        exchange='exert.fanout',
        routing_key='',
        body=message,
        # delivery_mode = 2 声明消息在队列中持久化，delivery_mod = 1 消息非持久化。
        properties=pika.BasicProperties(delivery_mode = 2),
    )
    print(message)
connection.close()