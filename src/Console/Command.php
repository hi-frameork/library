<?php

namespace Library\Console;

use Hi\Kernel\Argument;
use Hi\Kernel\Console\Command as ConsoleCommand;
use Library\Coroutine;
use Swoole\Event;

abstract class Command extends ConsoleCommand
{
    /**
     * 是否在协程中运行
     */
    protected $runInCoroutine = false;

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

    public function setRunInCoroutine(bool $runInCoroutine): self
    {
        $this->runInCoroutine = $runInCoroutine;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function boot(Argument $argument)
    {
        if ($this->runInCoroutine) {
            Coroutine::create(fn () => parent::boot($argument));
            Event::wait();
        } else {
            parent::boot($argument);
        }
    }
}
