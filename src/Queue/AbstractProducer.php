<?php

namespace Library\Queue;

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
     * 生产者构造方法
     *
     * @param array $data 生产者数据
     */
    public function __construct(
        protected ?array $data = null,
        protected bool $batch = false,
    ) {
    }

    /**
     * 返回生产者数据
     */
    public function getData(): array
    {
        return $this->data;
    }
}
