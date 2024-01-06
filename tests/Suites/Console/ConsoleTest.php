<?php

namespace Tests\Suites\Console;

use Library\Attribute\Console as AttributeConsole;
use Library\Console;
use PHPUnit\Framework\TestCase;

class ConsoleTest extends TestCase
{
    public function testConsoleAttribute()
    {
        $attr = (new AttributeConsole('command', 'title'));
        $this->assertSame('command', $attr->command);
        $this->assertSame('title', $attr->title);
        $this->assertSame(false, $attr->coroutine);
    }

    public function testLoadCommand()
    {
        $commands = [
            \Tests\Suites\Console\Stubs\Command::class,
            \Tests\Suites\Console\Stubs\CommandCoroutine::class,
            \Tests\Suites\Console\Stubs\CommandAttribute::class,
            \Tests\Suites\Console\Stubs\CommandAttributeCoroutine::class,
        ];

        $console = (new ConsoleStub(kernel()->getContainer()));
        $console->withCommands($commands)->loadCommand();

        $this->assertSame($commands, $console->getRegisters());
        $this->assertSame([], $console->getCommands());
    }
}

/**
 * @method self withCommands(array $a)
 */
class ConsoleStub extends Console
{
    public function loadCommand(): self
    {
        return parent::loadCommand();
    }
}
