<?php

namespace Library\Http;

use Exception;
use Hi\Http\Router as HttpRouter;
use Hi\Kernel\Attribute\Reader as AttributeReader;
use Library\Attribute\Types\Http;
use Library\Attribute\Types\Route;
use Library\Http\Router\InputClassProperty;
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
    protected array $routeAttribtes;

    /**
     * 已找到的路由类
     *
     * @var string[]
     */
    protected array $routerClasses;

    /**
     * Router Construct
     *
     * @param string $routeFilePath 路由文件所在目录路径
     */
    public function __construct(protected string $routeFilePath)
    {
        parent::__construct();
        $this->findRouteClasses();
    }

    /**
     * 从路由文件所在目录中查找路由类
     */
    protected function findRouteClasses(): void
    {
        try {
            $this->routerClasses = File::scanDirectoryClass($this->routeFilePath);
        } catch (Exception $e) {
            throw new RuntimeException('路由文件加载失败，' . $e->getMessage());
        }
    }

    /**
     * 返回路由文件所在目录路径
     */
    public function getRouteFilePath(): string
    {
        return $this->routeFilePath;
    }

    /**
     * 返回已找到的路由类
     */
    public function getRouteClasses(): array
    {
        return $this->routerClasses;
    }

    /**
     * 返回已解析的路由注解类
     */
    public function getRouteAttributes(): array
    {
        return $this->routeAttribtes;
    }

    /**
     * 解析路由类并加载路由规则
     */
    public function load(): static
    {
        try {
            foreach ($this->routerClasses as $className => $classFile) {
                $this->routeAttribtes[$className] = $this->parseClassAttribute($className);
                $this->loadAttributeRoute($this->routeAttribtes[$className]);
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
    private function parseClassAttribute(string $className): RouteClass
    {
        $reflectionClass = new ReflectionClass($className);

        $routeAttribtes = AttributeReader::getClassAttribute($reflectionClass, Route::class);
        if (!$routeAttribtes) {
            throw new RuntimeException("类 '{$className}' 必须声明路由注解, 请使用 " . Route::class . " 注解");
        }

        $class = new RouteClass($className, $routeAttribtes);

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $class->appendMethod($this->parseClassMethodAttribute($reflectionMethod));
        }

        return $class;
    }

    /**
     * 解析路由方法注解
     */
    private function parseClassMethodAttribute(ReflectionMethod $method): RouteClassMethod
    {
        $routeAttribtes = AttributeReader::getMethodAttribute($method, Route::class);
        if (!$routeAttribtes) {
            throw new RuntimeException(
                "方法 '{$method->class}::{$method->name}' 必须声明路由注解, 请使用 " . Route::class . " 注解"
            );
        }

        $routeClassMethod = new RouteClassMethod($method->name, $routeAttribtes);

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
            AttributeReader::hasClassAttribute($reflectionClass, Http::class),
            true
        );

        if ($routeClassMethodParameter->injectable) {
            foreach ($reflectionClass->getProperties() as $reflectionProperty) {
                $routeClassMethodParameter->appendProperty(new InputClassProperty(
                    $reflectionProperty->getName(),
                    $reflectionProperty->getType()->getName(),
                    $reflectionProperty->getType()->allowsNull(),
                    $reflectionProperty->getDefaultValue(),
                    AttributeReader::getPropertyAttribute($reflectionProperty, Http::class),
                ));
            }
        }

        return $routeClassMethodParameter;
    }

    /**
     * 加载路由规则
     */
    public function loadAttributeRoute(RouteClass $routeClass): void
    {
        $this->group($routeClass->attribute->prefix, function () use ($routeClass) {
            $instance = $routeClass->newInstance();
            foreach ($routeClass->methods as $method) {
                $handle = [$instance, $method->name];
                $this->mount(
                    $method->attribute->method,
                    $method->attribute->pattern,
                    $handle,
                    [
                        'parameters' => $method->parameters,
                        'attribute'  => $method->attribute,
                    ]
                );
            }
        });
    }
}
