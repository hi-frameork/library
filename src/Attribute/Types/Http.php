<?php

namespace Library\Attribute\Types;

use Attribute;

/**
 * HTTP 请求参数注解
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Http
{
    /**
     * 参数数据源
     */
    public string $sourceFrom;

    /**
     * 参数名称
     */
    public string $sourceName;

    /**
     * 参数名称
     *
     * @var string
     */
    public string $name;

    /**
     * 参数类型
     *
     * @var string
     */
    public string $type;

    public function __construct(
        public string $source = '',
        public string $desc = '',
        public string $rule = '',
        public string $default = '',
    ) {
        $parts            = explode('.', $this->source);
        $this->sourceFrom = strtolower($parts[0] ?? '');
        $this->sourceName = $parts[1] ?? '';
    }
}
