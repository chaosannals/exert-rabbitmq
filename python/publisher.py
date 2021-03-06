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
    exchange = 'python.exert',
    durable = True, # 持久化
)
result = channel.queue_declare(
    queue='python-exert',
    durable=True, # 持久化
)
for i in range(100):
    message = json.dumps({'no': f"1000{i}"})
    # 向队列插入数值 routing_key是队列名
    channel.basic_publish(
        exchange='',
        routing_key='python-exert',
        body=message,
        # delivery_mode = 2 声明消息在队列中持久化，delivery_mod = 1 消息非持久化。
        properties=pika.BasicProperties(delivery_mode = 2),
    )
    print(message)
connection.close()
