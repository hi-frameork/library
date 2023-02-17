<?php

namespace Library\Attribute\Types;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Http
{
    public string $sourceFrom;

    public string $sourceName;

    public string $name;

    public string $type;

    public bool $allowNull;

    public function __construct(
        public string $source = '',
        public string $desc = '',
        public string $rule = '',
        public string $default = '',
    ) {
        $this->parseSource();
    }

    private function parseSource(): void
    {
        $parts            = explode('.', $this->source);
        $this->sourceFrom = strtolower($parts[0] ?? '');
        $this->sourceName = $parts[1] ?? '';
    }
}
