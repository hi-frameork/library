<?php

namespace Library;

use Hi\Kernel\Attribute\Reader;
use Hi\Kernel\Console as KernelConsole;
use Hi\Kernel\Console\Command;
use Library\Attribute\Console as AttributeConsole;
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
            $instance = $this->continer->get($definition);
            if ($instance instanceof Command) {
                /** @var \Library\Console\Command $instance */
                $reflectionClass = new ReflectionClass($instance);
                /** @var ?AttributeConsole $classAttribute */
                $classAttribute = Reader::getClassAttribute($reflectionClass, AttributeConsole::class);
                if ($classAttribute) {
                    $classAttribute->command && $instance->setCommand($classAttribute->command);
                    $classAttribute->desc    && $instance->setTitle($classAttribute->desc);
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
