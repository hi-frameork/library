<?php

namespace Library\Attribute\Types;

use Attribute;

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
     * @param string $prefix     路由前缀，此类所有方法路由会自动添加该前缀
     * @param string $desc       前缀说明
     * @param string $middleware 命名中间件，以逗号分割多个中间件
     * @param string $get        HTTP GET
     * @param string $post       HTTP POST
     * @param string $put        HTTP PUT
     * @param string $delete     HTTP DELETE
     * @param string $patch      HTTP PATCH
     * @param string $options    HTTP OPTIONS
     * @param string $head       HTTP HEAD
     * @param string $middleware 命名中间件，以逗号分割多个中间件
     * @param string $desc       前缀说明
     * @param bool   $auth       是否需要身份认证
     * @param string $validate   验证器
     */
    public function __construct(
        string $get = '',
        string $post = '',
        string $put = '',
        string $delete = '',
        string $patch = '',
        string $options = '',
        string $head = '',
        public string $prefix = '',
        public string $middleware = '',
        public string $desc = '',
        public bool $auth = true,
        public string $validate = '',
    ) {
        [$this->method, $this->pattern] = match (true) {
            (bool) $get     => ['GET'     , $get],
            (bool) $post    => ['POST'    , $post],
            (bool) $put     => ['PUT'     , $put],
            (bool) $delete  => ['DELETE'  , $delete],
            (bool) $patch   => ['PATCH'   , $patch],
            (bool) $options => ['OPTIONS' , $options],
            (bool) $head    => ['HEAD'    , $head],
            default         => [''        , ''],
        };
    }
}
