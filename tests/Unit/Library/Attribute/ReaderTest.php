<?php

namespace Tests\Unit\Library\Attribute;

use Hi\Kernel\Attribute\Reader;
use Library\Attribute\Types\Http;
use Library\Attribute\Types\Route;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Tests\Asset\RouteAsset;

class ReaderTest extends TestCase
{
    // 测试获取路由类注解
    public function testGetClassAttribute()
    {
        $reflectionClass = new ReflectionClass(RouteAsset::class);
        /** @var Route $attribute */
        $attribute = Reader::getClassAttribute($reflectionClass, Route::class);
        // print_r($attribute);
        $this->assertSame('/api/asset', $attribute->prefix);
        $this->assertSame('route class', $attribute->desc);
    }

    // 测试获取路由类方法注解
    public function testGetMethodAttribute(): ReflectionMethod
    {
        $reflectionClass = new ReflectionClass(RouteAsset::class);
        $reflectionClass->getMethods();

        $post = $reflectionClass->getMethod('post');
        /** @var Route $postAttribute */
        $postAttribute = Reader::getMethodAttribute($post, Route::class);
        // print_r($postAttribute);
        $this->assertSame('POST', $postAttribute->method);
        $this->assertSame('/post-path', $postAttribute->pattern);
        $this->assertSame('route method post', $postAttribute->desc);

        $get = $reflectionClass->getMethod('get');
        /** @var Route $getAttribute */
        $getAttribute = Reader::getMethodAttribute($get, Route::class);
        // print_r($getAttribute);
        $this->assertSame('GET', $getAttribute->method);
        $this->assertSame('/get-path', $getAttribute->pattern);
        $this->assertSame('route method get', $getAttribute->desc);

        return $post;
    }

    /**
     * @depends testGetMethodAttribute
     */
    public function testGetPropertyAttribute(ReflectionMethod $method)
    {
        foreach ($method->getParameters() as $parameter) {
            $class = new ReflectionClass($parameter->getType()->getName());

            $property = $class->getProperty('id');
            /** @var Http $attribute */
            $attribute = Reader::getPropertyAttribute($property, Http::class);
            $this->assertSame('post', $attribute->sourceFrom);
            $this->assertSame('id', $attribute->sourceName);
            $this->assertSame('ID', $attribute->desc);
            $this->assertSame('ranger:1-9', $attribute->rule);
            // print_r($attribute);

            $property = $class->getProperty('name');
            /** @var Http $attribute */
            $attribute = Reader::getPropertyAttribute($property, Http::class);
            $this->assertSame('post', $attribute->sourceFrom);
            $this->assertSame('name', $attribute->sourceName);
            $this->assertSame('名称', $attribute->desc);
            // print_r($attribute);
        }
    }
}
