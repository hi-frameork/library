<?php

namespace Tests\Suites\Console\Stubs;

use Hi\Kernel\Argument;
use Library\Console\Command;
use Library\Attribute\Console;

#[Console(command: "command-attribute-coroutine", title: "Command Attribute Coroutine", coroutine: true)]
class CommandAttributeCoroutine extends Command
{
    public function execute(Argument $argument): bool
    {
        return true;
    }
}
