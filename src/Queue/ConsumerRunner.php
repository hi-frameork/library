<?php

namespace Library\Queue;

use Library\System\Process;

/**
 * 消费者运行器
 */
class ConsumerRunner
{
    /**
     * @var array<string, AbstractConsumer>
     */
    protected array $consumers = [];

    /**
     * 启动消费者
     */
    public function run(?string $aliasOrClassName = null): void
    {
        /** @var Process[] */
        $processes = [];
        foreach ($this->getConsumers($aliasOrClassName) as $consumner) {
            $process = new Process(fn () => $consumner->execute());
            $process->start();
            $processes[$process->pid] = $process;
        }
    }

    /**
     * 获取消费者
     *
     * @return AbstractConsumer[]
     */
    public function getConsumers(?string $aliasOrClassName = null)
    {
        if (is_null($aliasOrClassName)) {
            return null;
        }

        if (isset($this->consumers[$aliasOrClassName])) {
            return $this->consumers[$aliasOrClassName];
        }

        if (class_exists($aliasOrClassName)) {
            $consumer = new $aliasOrClassName();
            if (!$consumer instanceof AbstractConsumer) {
                throw new \Exception(sprintf('Consumer class %s must be an instance of %s', $aliasOrClassName, AbstractConsumer::class));
            }
            $this->consumers[$aliasOrClassName] = $consumer;

            return $consumer;
        }

        throw new \Exception(sprintf('Consumer class %s not found', $aliasOrClassName));
    }
}
