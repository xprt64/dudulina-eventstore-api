<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\Middleware\Exception\InvalidMiddleware;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;

/**
 * Utility that creates a middleware from a middleware, string, array of middlewares or callables (function or invokable)
 */
class Factory
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    public function factoryMiddleware($middleware): MiddlewareInterface
    {
        if ($middleware instanceof MiddlewareInterface) {
            return $middleware;
        }

        if (is_string($middleware)) {
            return new LazyLoadingMiddleware($this->container, $middleware);
        }

        if (is_array($middleware)) {
            return $this->combineMiddlewares($middleware);
        }

        if (is_callable($middleware)) {
            return new CallableMiddleware($middleware);
        }

        throw new InvalidMiddleware($middleware);
    }

    public function combineMiddlewares(array $middlewares)
    {
        $pipe = new Pipe($this);

        foreach ($middlewares as $item) {
            $pipe->pipe($this->factoryMiddleware($item));
        }

        return $pipe;
    }
}