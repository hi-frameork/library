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
        $inputAction = $argument->getAction();

        [$actionDescriptions, $actionClosures] = $this->loadActions();
        if (!isset($actionClosures[$inputAction])) {
            $this->actions = array_merge($this->actions, $actionDescriptions);
            $this->display();

            return;
        }

        /** @var Action $attribute */
        $attribute = $actionClosures[$inputAction]['attribute'];
        $closure   = $actionClosures[$inputAction]['action'];
        if ($attribute->coroutine) {
            Coroutine::create(fn () => $this->{$attribute->pre}() && $closure($argument));
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
}
