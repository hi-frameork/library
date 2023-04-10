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
     */
    public function __construct(
        public string $prefix = '',
        protected string $get = '',
        protected string $post = '',
        protected string $put = '',
        protected string $delete = '',
        protected string $patch = '',
        protected string $options = '',
        protected string $head = '',
        protected string $middleware = '',
        protected string $desc = '',
        protected bool $auth = true,
    ) {
        if ($get) {
            $this->method  = 'GET';
            $this->pattern = $get;

            return;
        }

        if ($post) {
            $this->method  = 'POST';
            $this->pattern = $post;

            return;
        }

        if ($put) {
            $this->method  = 'PUT';
            $this->pattern = $put;

            return;
        }

        if ($delete) {
            $this->method  = 'DELETE';
            $this->pattern = $delete;

            return;
        }

        if ($patch) {
            $this->method  = 'PATCH';
            $this->pattern = $patch;

            return;
        }

        if ($options) {
            $this->method  = 'OPTIONS';
            $this->pattern = $options;

            return;
        }

        if ($head) {
            $this->method  = 'HEAD';
            $this->pattern = $head;

            return;
        }
    }
}
