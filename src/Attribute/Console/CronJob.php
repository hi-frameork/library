<?php

namespace Library\Attribute\Console;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class CronJob
{
    public function __construct(
        public string $schedule,
        public string $action,
        public bool $coroutine = true,
        public string $description = '',
    ) {
    }
}
