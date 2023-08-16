<?php

namespace Library\Queue;

use longlang\phpkafka\Config\CommonConfig;

/**
 * 队列基类
 */
abstract class AbstractQueue
{
    /**
     * 队列连接
     */
    protected string $connection = '';

    /**
     * Topic - 枚举类型需要使用继承至 TopicInterface 的枚举类
     */
    protected TopicInterface $topic;

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

    /**
     * 返回 Topic 名称
     */
    public function getTopic(): string
    {
        return $this->topic->value;
    }

    /**
     * 返回队列使用的配置
     */
    public function getConfig()
    {
        return $this->config;
    }
}
