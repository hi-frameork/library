<?php

namespace Tests\Suites\Console\Stubs;

use Hi\Kernel\Argument;
use Library\Attribute\Console;
use Library\Console\Command;

#[Console(command: 'command-attribute', title: 'Command Attribute')]
class CommandAttribute extends Command
{
    public function execute(Argument $argument): bool
    {
        return true;
    }
}
