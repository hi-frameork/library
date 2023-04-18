<?php

namespace Library\Http;

use Exception;
use Hi\Http\Router as HttpRouter;
use Hi\Kernel\Attribute\Reader;
use Library\Attribute\Types\Route;
use Library\Http\Router\InputClassProperty;
use Library\Http\Router\MiddlewareLoader;
use Library\Http\Router\RouteClass;
use Library\Http\Router\RouteClassMethod;
use Library\Http\Router\RouteClassMethodParameter;
use Library\System\File;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;

class Router extends HttpRouter
{
    /**
     * 路由注解类
     *
     * @var RouteClass[]
     */
    protected array $routeAttributes;

    /**
     * 中间件注解类
     */
    protected MiddlewareLoader $middlewareLoader;

    /**
     * 已找到的路由类
     *
     * @var string[]
     */
    protected array $classes = [];

    /**
     * Router Construct
     *
     * @param string[] $pathes 路由文件所在目录路径
     */
    public function __construct(...$pathes)
    {
        parent::__construct();
        $this->findRouteClasses($pathes);

        $this->middlewareLoader = new MiddlewareLoader(
            array_keys($this->classes)
        );
    }

    /**
     * 从路由文件所在目录中查找路由类
     */
    protected function findRouteClasses(array $pathes): void
    {
        foreach ($pathes as $path) {
            $this->classes += File::scanDirectoryClass($path);
        }
    }

    /**
     * 返回已找到的路由类
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * 返回已解析的路由注解类
     */
    public function getRouteAttributes(): array
    {
        return $this->routeAttributes;
    }

    /**
     * 解析路由类并加载路由规则
     */
    public function load(): static
    {
        try {
            foreach ($this->classes as $className => $classFile) {
                $routeClass = $this->parseAttribute($className);
                if (!$routeClass) {
                    continue;
                }
                $this->routeAttributes[$className] = $routeClass;
                $this->loadAttributeRoute($routeClass);
            }
        } catch (Exception $e) {
            throw new RuntimeException(
                "路由文件解析失败: class[{$className}] file[{$classFile}] reason[{$e->getMessage()}]",
                $e->getCode(),
                $e
            );
        }

        return $this;
    }

    /**
     * 解析路由类注解
     */
    private function parseAttribute(string $class): ?RouteClass
    {
        $reflectionClass = new ReflectionClass($class);

        // 如果类没有路由注解，代表非路由类
        $attribute = Reader::getClassAttribute($reflectionClass, Route::class);
        if (!$attribute) {
            return null;
        }

        $routeClass = new RouteClass($class, $attribute);
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $routeClassMethod = $this->parseClassMethodAttribute($reflectionMethod);

            // 如果路由类注解中声明了 CORS 配置, 将其应用到路由方法注解中
            // 如果路由方法注解中声明了 CORS 配置，以路由方法注解中的配置为准
            if ($routeClass->attribute->cors && !$routeClassMethod->attribute->cors) {
                $routeClassMethod->attribute->cors = $routeClass->attribute->cors;
            }

            // 如果路由类注解中声明了中间件, 将其应用到路由方法注解中
            if ($routeClass->attribute->middleware) {
                $routeClassMethod->attribute->appendMiddleware($routeClass->attribute->middleware);
            }

            $routeClass->appendMethod($routeClassMethod);
        }

        return $routeClass;
    }

    /**
     * 解析路由方法注解
     */
    private function parseClassMethodAttribute(ReflectionMethod $method): RouteClassMethod
    {
        $attributes = Reader::getMethodAttribute($method, Route::class);
        if (!$attributes) {
            throw new RuntimeException(
                "方法 '{$method->class}::{$method->name}' 必须声明路由注解, 请使用 " . Route::class . " 进行注解"
            );
        }

        $routeClassMethod = new RouteClassMethod($method->name, $attributes);

        foreach ($method->getParameters() as $parameter) {
            $routeClassMethod->appendParameter($this->parseClassMethodParameterAttribute($parameter));
        }

        return $routeClassMethod;
    }

    /**
     * 解析路由方法参数注解
     */
    private function parseClassMethodParameterAttribute(ReflectionParameter $parameter): RouteClassMethodParameter
    {
        $typeName = $parameter->getType()->getName();

        if ($parameter->getType()->isBuiltin() || !class_exists($typeName, true)) {
            return new RouteClassMethodParameter($parameter->getName(), $typeName);
        }

        $reflectionClass = new ReflectionClass($typeName);

        $routeClassMethodParameter = new RouteClassMethodParameter(
            $parameter->getName(),
            $typeName,
            Reader::hasClassAttribute($reflectionClass, Http::class),
            true
        );

        if ($routeClassMethodParameter->injectable) {
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $routeClassMethodParameter->appendProperty(new InputClassProperty(
                    $reflectionProperty->getName(),
                    $reflectionProperty->getType()->getName(),
                    $reflectionProperty->getType()->allowsNull(),
                    $reflectionProperty->getDefaultValue(),
                    Reader::getPropertyAttribute($reflectionProperty, Http::class),
                ));
            }
        }

        return $routeClassMethodParameter;
    }

    /**
     * 加载路由规则
     */
    protected function loadAttributeRoute(RouteClass $class): void
    {
        $this->group($class->attribute->prefix, function () use ($class) {
            foreach ($class->methods as $method) {
                $attribute = $method->attribute;
                // 路由需要支持跨域访问，为路由创建 OPTIONS 请求路由
                if ($attribute->cors) {
                    $this->mount('OPTIONS', $attribute->pattern, fn () => '', [
                        'attribute'  => $attribute,
                        'middleware' => $this->middlewareLoader->get($attribute->cors),
                    ]);
                }

                $handle = [$class->newInstance(), $method->name];

                $this->mount(
                    $attribute->method,
                    $attribute->pattern,
                    $handle,
                    [
                        'parameters' => $method->parameters,
                        'attribute'  => $attribute,
                        'middleware' => $this->middlewareLoader->get($attribute->middleware),
                    ]
                );
            }
        });
    }

    /**
     * 返回路由树
     */
    public function getRouteTree()
    {
        return $this->tree;
    }
}
