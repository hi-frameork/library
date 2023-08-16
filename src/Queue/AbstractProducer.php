<?php

namespace Library\Queue;

use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

/**
 * 生产者基类
 *
 * @property ProducerConfig $config
 * @method ProducerConfig getConfig()
 */
abstract class AbstractProducer extends AbstractQueue
{
    /**
     * 消息投递目标分区
     */
    protected ?int $partitionIndex = null;

    /**
     * Topic ack 策略
     */
    protected int $acks = -1;

    /**
     * 消息头部
     */
    protected array $headers = [];

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
        $this->config->setAcks($this->getAcks());
        $this->config->setUpdateBrokers(true); // 自动更新 broker 服务器
        $this->config->setAutoCreateTopic(false); // 禁止自动创建 topic
        $this->config->setConnectTimeout(5);
        $this->config->setSendTimeout(5);

        // 默认底层统一以批量机制发送消息
        if (!$batch) {
            $this->data = [$data];
        }
    }

    /**
     * 返回目标分区
     */
    public function getPartitionIndex(): ?int
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

    /**
     * 返回消息 key
     *
     * 默认为 null，使用默认分区策略
     * 若需要自定义分区策略，重写该方法
     */
    public function getkey(): ?string
    {
        return null;
    }

    /**
     * 设置消息头部
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * 返回消息头部
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * 连接消息队列并批量发送消息
     */
    public function send(): bool
    {
        $messages = [];
        foreach ($this->data as $item) {
            $messages[] = new ProduceMessage(
                $this->getTopic(),
                json_encode($item),
                $this->getkey(),
                $this->getHeaders(),
                $this->getPartitionIndex()
            );
        }

        if ($messages) {
            $producer = (new Producer($this->config));
            $producer->sendBatch($messages);
            $producer->close();

            return true;
        }

        return false;
    }
}
