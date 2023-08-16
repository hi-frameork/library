<?php

namespace Library\Queue;

use Exception;

/**
 * 生产者运行器
 */
class ProducerRunner
{
    public function __construct(
        protected Config $config,
        protected Parser $parser
    ) {
    }

    /**
     * 执行生产者
     */
    public function run(AbstractProducer|string $producer, ?array $data = null): bool
    {
        // 如果是 TopicInterface 子类，那么就是枚举类型
        // 需要先通过枚举类型获取生产者组(相同枚举类型)
        if (is_string($producer)) {
            $defines   = $this->parser->get($producer);
            $producers = array_map(fn ($define) => new $define['class']($data), $defines);
        } else {
            $producers = [$producer];
        }

        /** @var AbstractProducer[] $producers */
        foreach ($producers as $producer) {
            // 检查连接名是否设置
            $connection = $producer->getConnection();
            if (!$connection) {
                $class = get_class($producer);

                throw new Exception("Class {$class} Queue connection name must be set");
            }
        }

        foreach ($producers as $producer) {
            // 为生产者设置 bootstrap 服务器
            $producer->getConfig()->setBootstrapServer(
                $this->config->get($connection)->bootstrapServers
            );

            $producer->send();
        }

        return true;
    }
}
