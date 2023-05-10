<?php

namespace Library\Attribute\Types;

use Attribute;

/**
 * HTTP 路由注解
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    /**
     * http 方法，例如：GET, POST, PUT
     */
    public string $method;

    /**
     * 路由路径
     */
    public string $pattern;

    /**
     * 路由中间件
     */
    public array $middleware = [];

    /**
     * @param string       $get        HTTP GET
     * @param string       $post       HTTP POST
     * @param string       $put        HTTP PUT
     * @param string       $delete     HTTP DELETE
     * @param string       $patch      HTTP PATCH
     * @param string       $options    HTTP OPTIONS
     * @param string       $head       HTTP HEAD
     * @param string       $prefix     路由前缀，此类所有方法路由会自动添加该前缀
     * @param string       $desc       前缀说明
     * @param string|array $middleware 命名中间件
     * @param bool         $auth       是否需要身份认证
     * @param string       $cors       跨域设置，指定中间件名称，例如：cors.default
     */
    public function __construct(
        string $get = '',
        string $post = '',
        string $put = '',
        string $delete = '',
        string $patch = '',
        string $options = '',
        string $head = '',
        string|array $middleware = [],
        public string $cors = '',
        public string $prefix = '',
        public string $desc = '',
        public bool $auth = true,
    ) {
        [$this->method, $this->pattern] = match (true) {
            (bool) $get     => ['GET',     $get],
            (bool) $post    => ['POST',    $post],
            (bool) $put     => ['PUT',     $put],
            (bool) $delete  => ['DELETE',  $delete],
            (bool) $patch   => ['PATCH',   $patch],
            (bool) $options => ['OPTIONS', $options],
            (bool) $head    => ['HEAD',    $head],
            default         => ['',        ''],
        };

        if ($middleware) {
            $this->middleware = is_array($middleware) ? $middleware : [$middleware];
        }
    }

    /**
     * 添加中间件
     */
    public function appendMiddleware(string|array $middleware): void
    {
        if (is_array($middleware)) {
            $this->middleware += $middleware;
        } else {
            $this->middleware[] = $middleware;
        }

        // 去重
        $this->middleware = array_unique($this->middleware);
    }
}
