<?php

namespace Library\Queue;

use Exception;
use Library\System\Process\Manager;

/**
 * 消费者运行器
 */
class ConsumerRunner
{
    public function __construct(
        protected Config $config,
        protected Parser $consunerParser
    ) {
    }

    /**
     * 启动消费者
     */
    public function run(?string $aliasOrClassName = null): void
    {
        // 进程管理器
        $pm = new Manager();

        $consumers = $this->consunerParser->get($aliasOrClassName);
        // 启动消费者(在子进程中执行)
        foreach ($consumers as $item) {
            $class = $item['class'];
            /** @var AbstractConsumer $consumer */
            $consumer = new $class();

            // 检查连接名与连接配置是否设置
            $connection = $consumer->getConnection();
            if (!$connection) {
                throw new Exception("Class {$class} connection name must be set");
            }
            // 为消费者设置 broker 服务器
            $consumer->getConfig()->setBrokers(
                $this->config->get($connection)->brokers
            );

            // 检查 topic 名称是否设置
            $topic = $consumer->getTopic();
            if (!$topic) {
                throw new Exception("Class {$class} topic must be set");
            }

            // 创建子进程启动消费者
            $pm->add(fn () => $consumer->execute(), true);
        }

        $pm->start();
    }
}
