<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Infrastructure\Http\Endpoint;


use Gica\Infrastructure\Http\Lib\CacheHeadersLib;
use Gica\Infrastructure\Http\Middleware\ContentNegociationMiddleware;
use Gica\Infrastructure\Http\ParameterInjecterMiddleware\ParameterInjecter;
use Psr\Container\ContainerInterface;

/**
 * It creates a middleware (instance of MiddlewareInterface) from a class name.
 * The middleware knows about caching.
 */
class CachingEndpointFactory extends EndpointFactory
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

    public function createEndpointMiddleware($className)
    {
        return new EndpointToMiddlewareCachedDecorator(
            $this->container->get(ContentNegociationMiddleware::class),
            $this->container,
            $className,
            $this->container->get(ParameterInjecter::class),
            $this->container->get(CacheHeadersLib::class)
        );
    }
}