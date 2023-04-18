<?php

namespace Library\Http\Router;

use Hi\Kernel\Attribute\Reader;
use Library\Attribute\Types\Middleware;
use ReflectionClass;

/**
 * 中间件加载器
 */
class MiddlewareLoader
{
    /**
     * 中间件解析结果
     */
    protected array $middlewares = [];

    /**
     * @param string[] $classes
     */
    public function __construct(protected array $classes)
    {
        $this->parse();
    }

    /**
     * 解析中间件注解
     */
    protected function parse()
    {
        foreach ($this->classes as $class) {
            $reflectionClass = new ReflectionClass($class);
            /** @var Middleware|null $attribute */
            $attribute = Reader::getClassAttribute($reflectionClass, Middleware::class);
            if (!$attribute) {
                continue;
            }

            // 中间件类名与中间件优先级映射
            // 在 get 方法中将会基于 priority 进行排序，值越小优先级越高
            $item = [
                'alias'    => $attribute->alias,
                'priority' => $attribute->priority,
                'class'    => $class,
            ];

            // 中间件别名与中间件类型映射
            $this->middlewares[$attribute->alias] = $item;
            $this->middlewares[$class]            = $item;

            // 中间件第一个字母作为分组
            // 例如：auth.login 会被分组到 auth 中
            $parts = explode('.', $attribute->alias);
            if (count($parts) > 1) {
                $this->middlewares[$parts[0]][] = $item;
            }
        }
    }

    /**
     * 获取中间件解析结果
     */
    public function getParsed(): array
    {
        return $this->middlewares;
    }

    /**
     * 根据中间件别名获取中间件类名
     * 中间件将会按照 priority 从小到大的顺序排序
     *
     * @return string[]
     */
    public function get(string|array $defines): array
    {
        if (is_string($defines)) {
            $defines = [$defines];
        }

        $classes = [];
        foreach ($defines as $define) {
            // 中间件定义不存在，生成告警并跳过
            if (!isset($this->middlewares[$define])) {
                trigger_error("中间件定义 '{$define}' 不存在", E_USER_WARNING);
                continue;
            }

            $middlewares = $this->middlewares[$define];
            // 存在 class 代表是单个中间件别名
            // 不存在 class 代表是中间件分组
            if (isset($middlewares['class'])) {
                // 处理单个中间件别名情况
                // 例如：auth.login
                $classes[] = $middlewares;
            } else {
                // 处理中间件分组情况
                // 例如：auth
                $classes += $middlewares;
            }
        }

        // 根据优先级 priority 对中间件进行排序，值越小优先级越高
        // 如果优先级相同，则按照中间件别名进行排序
        usort($classes, function ($a, $b) {
            if ($a['priority'] == $b['priority']) {
                return $a['alias'] <=> $b['alias'];
            }

            return $a['priority'] <=> $b['priority'];
        });

        return array_column($classes, 'class');
    }
}
