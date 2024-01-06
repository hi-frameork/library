<?php

namespace Library;

use Hi\Kernel\Attribute\Reader;
use Hi\Kernel\Console as KernelConsole;
use Library\Attribute\Console as AttributeConsole;
use Library\Console\Command;
use ReflectionClass;

class Console extends KernelConsole
{
    public function getRegisters()
    {
        return $this->registers;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    protected function loadCommand(): self
    {
        foreach ($this->registers as $definition) {
            $this->continer->attempt($definition, $definition);
        }

        foreach ($this->registers as $definition) {
            /** @var Command $instance */
            $instance = $this->continer->get($definition);
            if ($instance instanceof Command) {
                $reflectionClass = new ReflectionClass($instance);
                /** @var AttributeConsole */
                $classAttribute = Reader::getClassAttribute($reflectionClass, AttributeConsole::class);
                if ($classAttribute) {
                    $instance->setCommand($classAttribute->command);
                    $instance->setTitle($classAttribute->title);
                    $instance->setDescription($classAttribute->description);
                    $instance->setRunInCoroutine($classAttribute->coroutine);
                }

                if (!$instance->getCommand()) {
                    trigger_error($definition . ' command not set');
                } else {
                    $this->commands[$instance->getCommand()] = $instance;
                }
            } else {
                trigger_error($definition . ' must be instance of Command');
            }
        }

        return $this;
    }
}
