<?php

namespace Library\Console;

use Hi\Kernel\Argument;
use Hi\Kernel\Attribute\Reader;
use Hi\Kernel\Console\Command as ConsoleCommand;
use Library\Attribute\Console\Action;
use Library\Coroutine;
use ReflectionClass;
use Swoole\Event;

abstract class Command extends ConsoleCommand
{
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * 从注解中加载当前类所有 action
     */
    public function loadActions()
    {
        $actionDescriptions = [];
        $actionClosures     = [];
        $classReflection    = new ReflectionClass($this);
        foreach ($classReflection->getMethods() as $method) {
            /** @var Action $attribute */
            $attribute = Reader::getMethodAttribute($method, Action::class);
            if ($attribute) {
                // 记录 action 对应的描述并拼接 schedule 信息
                // 示例：
                //  create    生成 Ingress 路由配置 YAML 文件 > schedule[* * * *]
                $actionDescriptions[$attribute->action] = $attribute->desc . ($attribute->schedule ? " |> schedule: {$attribute->schedule}" : '');

                // 记录 action 对应的方法
                $actionClosures[$attribute->action] = [
                    'command'   => $this,
                    'attribute' => $attribute,
                    'action'    => $method->getClosure($this),
                ];
            }

            continue;
        }

        return [$actionDescriptions, $actionClosures];
    }

    /**
     * @inheritdoc
     */
    public function boot(Argument $argument)
    {
        [$actionDescriptions, $actionClosures] = $this->loadActions();

        $inputAction   = $argument->getAction();
        $this->actions = array_merge($this->actions, $actionDescriptions);
        if (!isset($this->actions[$inputAction])) {
            $this->display();

            return;
        }

        if (isset($actionClosures[$inputAction])) {
            /** @var Action $attribute */
            $attribute = $actionClosures[$inputAction]['attribute'];
            $closure   = $actionClosures[$inputAction]['action'];

            //  前置方法执行失败则直接退出
            if (!$this->{$attribute->pre}()) {
                throw new \RuntimeException('Pre method running failed.');
            }

            if ($attribute->replicas) {
                for (;;) $this->warpRun($attribute, $closure, $argument);
            } else {
                $this->warpRun($attribute, $closure, $argument);
            }
        } else {
            $this->init() && $this->execute($argument);
        }
    }

    /**
     * 执行 action
     */
    private function warpRun(Action $attribute, $closure, $argument)
    {
        if ($attribute->coroutine) {
            Coroutine::create(fn () => $closure($argument));
            Event::wait();
        } else {
            $closure($argument);
        }
    }

    public function execute(Argument $argument): bool
    {
        return true;
    }

    public function init(): bool
    {
        return true;
    }

    /**
     * 打印三列对齐方式的表格
     */
    protected function displayThreeColumns(array $data)
    {
        // 三列对齐方式打印
        // Action     Schedule     Description
        // cron-demo  */1 * * * *  定时任务示例
        $maxLen = array_map(
            fn ($item) => max(array_map(fn ($i) => strlen($i), $item)),
            array_map(null, ...$data)
        );
        foreach ($data as $item) {
            fwrite(STDOUT, implode('  ', array_map(fn ($i, $j) => str_pad($i, $j), $item, $maxLen)) . PHP_EOL);
        }
    }

    /**
     * 所有命令行遍历
     */
    protected function iterativeCommands(callable $callback): array
    {
        $schedules = [];

        /** @var Command[] */
        $commands = app('console')->getCommands();
        foreach ($commands as $command) {
            if (!$command instanceof Command) {
                continue;
            }

            [, $actionClosures] = $command?->loadActions();
            foreach ($actionClosures as $action) {
                if ($result = $callback($action['command'], $action['attribute'], $action['action'])) {
                    $schedules[] = $result;
                }
            }
        }

        return $schedules;
    }
}
