<?php

namespace Library\Console;

use Hi\Kernel\Argument;
use Library\Coroutine;

abstract class CommandAction
{
    /**
     * 调度时间表达式
     */
    protected string $schedule;

    /**
     * 操作名称
     */
    protected string $action;

    /**
     * 操作介绍
     */
    protected string $description = '';

    /**
     * 是否在协程中运行
     */
    protected bool $coroutine = true;

    public function setSchedule(string $schedule): self
    {
        $this->schedule = $schedule;
        return $this;
    }

    public function getSchedule(): string
    {
        return $this->schedule;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setCoroutine(bool $coroutine): self
    {
        $this->coroutine = $coroutine;
        return $this;
    }

    public function getCoroutine(): bool
    {
        return $this->coroutine;
    }

    /**
     * 启动命令
     */
    public function boot(Argument $argument)
    {
        if ($this->coroutine) {
            return Coroutine::create(fn () => $this->run($argument));
        }

        return $this->run($argument);
    }

    abstract protected function run(Argument $argument);
}
