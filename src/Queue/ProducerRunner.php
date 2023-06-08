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
    public function run(AbstractProducer|string $producer, ?array $data = null): void
    {
        if (is_string($producer)) {
            $defines   = $this->parser->get($producer);
            $producers = array_map(fn ($define) => new $define['class']($data), $defines);
        } else {
            $producers = [$producer];
        }

        /** @var AbstractProducer[] $producers */
        foreach ($producers as $producer) {
            // 检查连接名是否设置
            $class      = get_class($producer);
            $connection = $producer->getConnection();
            if (!$connection) {
                throw new Exception("Class {$class} connection name must be set");
            }

            // 为生产者设置 bootstrap 服务器
            $producer->getConfig()->setBootstrapServer(
                $this->config->get($connection)->bootstrapServers
            );

            $producer->send();
        }
    }
}
