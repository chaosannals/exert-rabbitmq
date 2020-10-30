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
result = channel.queue_declare(queue='python-exert')
for i in range(100):
    message = json.dumps({'no': f"1000{i}"})
    # 向队列插入数值 routing_key是队列名
    channel.basic_publish(
        exchange='',
        routing_key='python-exert',
        body=message
    )
    print(message)
connection.close()
