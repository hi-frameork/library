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
     * ```php
     * [
     * 'classes' => [
     *     'consunmer01' => [
     *         ['class' => $class, 'attribute' => $attribute],
     *     ]
     * 'aliases' => [
     *     'alias01' => [
     *         ['class' => $class, 'attribute' => $attribute],
     *         ['class' => $class, 'attribute' => $attribute],
     *     ]
     * ]
     * ```
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
     *
     * 相同注解别名视为同一组
     * 在消费者/生产者执行时如果通过别名操作将会将该组所有生产者/消费者都执行
     *
     * @param string Consumer|Producer $attributeClass
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

            // 将类添加到未分类数组中
            $this->parsed['unclassified'][] = $item;
            // 将类添加到类数组中
            $this->parsed['classes'][$class] = [$item];
            // 如果有别名，将类添加到别名数组中
            if ($attribute->alias) {
                $this->parsed['aliases'][$attribute->alias][] = $item;
            }
        }
    }

    /**
     * 获取类或别名对应的消费者或生产者类解析数据
     *
     * @throws NotFoundException
     */
    public function get(?string $aliasOrClassName = null): array
    {
        if (empty($aliasOrClassName)) {
            return $this->parsed['unclassified'];
        }

        if (isset($this->parsed['classes'][$aliasOrClassName])) {
            return $this->parsed['classes'][$aliasOrClassName];
        }

        if (isset($this->parsed['aliases'][$aliasOrClassName])) {
            return $this->parsed['aliases'][$aliasOrClassName];
        }

        throw new NotFoundException("Class or Alias '{$aliasOrClassName}' not found");
    }

    public function getParsed(): array
    {
        return $this->parsed;
    }
}
