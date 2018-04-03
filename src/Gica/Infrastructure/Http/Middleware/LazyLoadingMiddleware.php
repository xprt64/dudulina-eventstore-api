<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Transform a middleware represented by a class name into a MiddlewareInterface at processing time, not earlier
 */
class LazyLoadingMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string
     */
    private $middlewareClass;

    public function __construct(
        ContainerInterface $container,
        string $middlewareClass
    )
    {
        $this->container = $container;
        $this->middlewareClass = $middlewareClass;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->container->get($this->middlewareClass);

        if (!$middleware instanceof MiddlewareInterface) {
            throw new \InvalidArgumentException(sprintf("Middleware %s is not instance of %s", $this->middlewareClass, MiddlewareInterface::class));
        }

        return $middleware->process($request, $delegate);
    }
}