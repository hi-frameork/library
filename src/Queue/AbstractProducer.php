<?php

namespace Library\Queue;

use longlang\phpkafka\Producer\ProducerConfig;
use Throwable;

abstract class AbstractProducer
{
    /**
     * 队列连接
     */
    protected string $connection = 'default';

    /**
     * 消息投递目标分区
     * 默认为 -1 时，由 kafka sdk 预置默认行为决定
     */
    protected int $partitionIndex = -1;

    /**
     * Topic ack 策略
     */
    protected int $acks = -1;

    /**
     * Topic 枚举类型
     */
    protected TopicInterface $topic;

    protected ProducerConfig $config;

    /**
     * 生产者构造方法
     *
     * @param array $data 生产者数据
     */
    public function __construct(
        protected ?array $data = null,
        protected bool $batch = false,
    ) {
        $this->config = new ProducerConfig();
        $this->config->setAcks($this->acks);
    }

    /**
     * 返回生产者连接
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * 返回目标分区
     */
    public function getPartitionIndex(): int
    {
        return $this->partitionIndex;
    }

    /**
     * 返回 Topic ack 策略
     */
    public function getAcks(): int
    {
        return $this->acks;
    }

    /**
     * 返回生产者数据
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function send(): void
    {
        $pool = app('queue.kafka')->pool($this->connection);

        try {
            $con  = $pool->get();
            $con->send($this->topic->value, $this->data, null, [], $this->getPartitionIndex());
        } catch (Throwable $th) {
            throw $th;
        } finally {
            $pool->put($con);
        }
    }
}
