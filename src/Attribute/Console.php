<?php

namespace Library\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Console
{
    public function __construct(
        public string $command,
        public string $title,
        public bool $coroutine = false,
        public string $description = '',
    ) {
    }
}
