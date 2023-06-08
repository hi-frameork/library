<?php

namespace Library\Queue;

use Hi\Kernel\Attribute\Reader;
use Library\Attribute\Queue\Consumer;
use Library\Attribute\Queue\Producer;
use ReflectionClass;

/**
 * 队列消费者和生产者类解析器
 */
class Parser
{
    /**
     * @var array
     */
    protected array $parsed = [
        'classes' => [],
        'aliases' => [],
    ];

    public function __construct(array $classes, string $attributeClass)
    {
        $this->parse($classes, $attributeClass);
    }

    /**
     * 解析指定注解的类
     */
    protected function parse(array $classes, string $attributeClass)
    {
        foreach ($classes as $class) {
            $reflectionClass = new ReflectionClass($class);
            // 如果类没有注解，代表非生产者类
            /** @var Consumer|Producer $attribute */
            $attribute = Reader::getClassAttribute($reflectionClass, $attributeClass);
            if (!$attribute) {
                continue;
            }

            $item = [
                'class'     => $class,
                'attribute' => $attribute,
            ];

            $this->parsed['classes'][$class]              = [$item];
            $this->parsed['aliases'][$attribute->alias][] = $item;
        }
    }

    /**
     * 获取类或别名对应的消费者或生产者类解析数据
     */
    public function get(?string $aliasOrClassName = null): array
    {
        if (is_null($aliasOrClassName)) {
            return $this->parsed['classes'];
        }

        if (isset($this->parsed['classes'][$aliasOrClassName])) {
            return $this->parsed['classes'][$aliasOrClassName];
        }

        if (isset($this->parsed['aliases'][$aliasOrClassName])) {
            return $this->parsed['aliases'][$aliasOrClassName];
        }

        throw new NotFoundException("Class or alias '{$aliasOrClassName}' not found");
    }

    public function getParsed(): array
    {
        return $this->parsed;
    }
}
