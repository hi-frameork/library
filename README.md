# library

## kafka

使用 Strimzi 提供 kafka operator

### 安装

```bash
kubectl create namespace kafka
kubectl create -f 'https://strimzi.io/install/latest?namespace=kafka' -n kafka
kubectl get pod -n kafka --watch
kubectl logs deployment/strimzi-cluster-operator -n kafka -f
```

创建 kafka 集群

```bash
# Apply the `Kafka` Cluster CR file
kubectl apply -f https://strimzi.io/examples/latest/kafka/kafka-persistent-single.yaml -n kafka
kubectl wait kafka/my-cluster --for=condition=Ready --timeout=300s -n kafka 
```

发送和接收消息测试

```bash
# 在集群运行时，运行一个简单的制作者向Kafka主题发送消息（主题会自动创建）：
kubectl -n kafka run kafka-producer -ti --image=quay.io/strimzi/kafka:0.34.0-kafka-3.4.0 --rm=true --restart=Never -- bin/kafka-console-producer.sh --bootstrap-server my-cluster-kafka-bootstrap:9092 --topic my-topic

#并在另一个终端接收它们，请运行：
kubectl -n kafka run kafka-consumer -ti --image=quay.io/strimzi/kafka:0.34.0-kafka-3.4.0 --rm=true --restart=Never -- bin/kafka-console-consumer.sh --bootstrap-server my-cluster-kafka-bootstrap:9092 --topic my-topic --from-beginning
```

### 卸载

```bash
kubectl -n kafka delete $(kubectl get strimzi -o name -n kafka)
kubectl -n kafka delete -f 'https://strimzi.io/install/latest?namespace=kafka'
```
