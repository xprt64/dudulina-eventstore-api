<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Endpoint;


use Gica\Infrastructure\Http\Middleware\ContentNegociationMiddleware;
use Gica\Infrastructure\Http\ParameterInjecterMiddleware\ParameterInjecter;
use Psr\Container\ContainerInterface;

/**
 * It creates a middleware (instance of MiddlewareInterface) from a class name
 */
class EndpointFactory
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
        return new EndpointToMiddlewareDecorator(
            $this->container->get(ContentNegociationMiddleware::class),
            $this->container,
            $className,
            $this->container->get(ParameterInjecter::class)
            );
    }
}