<?php

namespace Tests\Suites\Console\Stubs;

use Hi\Kernel\Argument;
use Library\Console\Command as ConsoleCommand;

class Command extends ConsoleCommand
{
    public function execute(Argument $argument): bool
    {
        return true;
    }
}
