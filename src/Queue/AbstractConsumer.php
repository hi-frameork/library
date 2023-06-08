<?php

namespace Library\Queue;

use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;

/**
 * @property ConsumerConfig $config
 * @method ConsumerConfig getConfig()
 */
abstract class AbstractConsumer extends AbstractQueue
{
    /**
     * 消费者初始化/配置初始化
     * 子类需要更多配置时，重写该方法
     *
     * 例如：
     * ```php
     *  parent::__construct();
     *
     *  $this->config->setBroker('127.0.0.1:9092');
     *  $this->config->setTopic('test'); // 主题名称
     *  $this->config->setGroupId('testGroup'); // 分组ID
     *  $this->config->setClientId('test_custom'); // 客户端ID
     *  $this->config->setGroupInstanceId('test_custom'); // 分组实例ID
     * ````
     */
    public function __construct()
    {
        $this->config = new ConsumerConfig();
        $this->config->setTopic($this->topic->value);
        $this->config->setInterval(0.1);
    }

    public function setBroker(string $broker): void
    {
        $this->config->setBroker($broker);
    }

    public function execute(): void
    {
        $consumer = (new Consumer($this->config, [$this, 'consume']));
        $consumer->start();
    }

    /**
     * 执行消费业务处理
     */
    abstract public function consume(?ConsumeMessage $message): void;
}
