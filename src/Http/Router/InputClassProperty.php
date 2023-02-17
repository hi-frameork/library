<?php

namespace Library\Http\Router;

use Library\Attribute\Types\Http;

class InputClassProperty
{
    public function __construct(
        public string $name,
        public string $type,
        public bool $allowsNull,
        public $defaultValue = null,
        public ?Http $attribute = null,
    ) {
    }
}
