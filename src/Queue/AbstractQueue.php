<?php

namespace Library\Queue;

use longlang\phpkafka\Config\CommonConfig;

abstract class AbstractQueue
{
    /**
     * 队列连接
     */
    protected string $connection = 'kafka.default';

    /**
     * Topic - 枚举类型
     */
    protected TopicInterface $topic;

    /**
     * 生产者/消费者描述
     */
    protected string $description = '';

    /**
     * 队列配置
     */
    protected CommonConfig $config;

    /**
     * 返回连接名称
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    public function getTopic(): string
    {
        return $this->topic->value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getConfig()
    {
        return $this->config;
    }
}
