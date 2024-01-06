<?php

namespace Tests\Suites\Console\Stubs;

use Hi\Kernel\Argument;
use Library\Console\Command;

class CommandCoroutine extends Command
{
    public function execute(Argument $argument): bool
    {
        return true;
    }
}
