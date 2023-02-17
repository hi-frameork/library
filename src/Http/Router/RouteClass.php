<?php

namespace Library\Http\Router;

use Library\Attribute\Types\Route;

class RouteClass
{
    /**
     * @param RouteClassMethod[] $methods
     */
    public function __construct(
        public string $name,
        public Route $attribute,
        public array $methods = [],
    ) {
    }

    public function newInstance(): object
    {
        return new $this->name();
    }

    public function appendMethod(RouteClassMethod $method): void
    {
        $this->methods[] = $method;
    }
}
