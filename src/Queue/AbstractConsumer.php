<?php

namespace Library\Queue;

use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer;
use longlang\phpkafka\Consumer\ConsumerConfig;

/**
 * 消费者基类
 *
 * @property ConsumerConfig $config
 * @method ConsumerConfig getConfig()
 */
abstract class AbstractConsumer extends AbstractQueue
{
    protected Consumer $consumer;

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
        $this->config->setGroupId(static::class);
        $this->config->setAutoCreateTopic(false);
        $this->config->setConnectTimeout(5);
    }

    /**
     * 创建消费者实例并执行消费
     */
    public function execute(bool $loop = true): void
    {
        if ($loop) {
            $this->consumer = (new Consumer($this->config, [$this, 'consume']));
        } else {
            $this->consumer = (new Consumer($this->config, function (?ConsumeMessage $meesage) {
                $this->consume($meesage);
                $consumer = $meesage->getConsumer();
                $consumer->stop();
            }));
        }

        $this->consumer->start();
        $this->consumer->close();
    }

    /**
     * 关闭消费者
     */
    public function close(): void
    {
        $this->consumer->close();
    }

    /**
     * 执行消费业务处理
     */
    abstract public function consume(?ConsumeMessage $message);
}
