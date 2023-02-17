<?php

namespace Library\Http\Router;

use Library\Attribute\Types\Route;

class RouteClassMethod
{
    /**
     * @param RouteClassMethodparameter[] $parameters
     */
    public function __construct(
        public string $name,
        public Route $attribute,
        public array $parameters = [],
    ) {
    }

    public function appendParameter(RouteClassMethodparameter $parameter): void
    {
        $this->parameters[$parameter->name] = $parameter;
    }
}
