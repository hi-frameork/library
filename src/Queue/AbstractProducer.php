<?php

namespace Library\Queue;

use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\ProducerConfig;

/**
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
        $this->config->setUpdateBrokers(true);

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

    public function getkey(): ?string
    {
        return null;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function send(): void
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

        (new Producer($this->config))->sendBatch($messages);
    }
}
