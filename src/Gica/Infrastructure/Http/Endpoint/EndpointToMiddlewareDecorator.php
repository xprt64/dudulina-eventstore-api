<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Endpoint;


use Gica\Infrastructure\Http\Middleware\ContentNegociationMiddleware;
use Gica\Infrastructure\Http\ParameterInjecterMiddleware\ParameterInjecter;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware that wraps a REST endpoint. It responds with a negotiated HTTP response
 */
class EndpointToMiddlewareDecorator implements MiddlewareInterface
{
    /**
     * @var ContentNegociationMiddleware
     */
    private $contentNegociationMiddleware;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var string
     */
    private $endpointName;
    /**
     * @var ParameterInjecter
     */
    private $parameterInjecter;

    public function __construct(
        ContentNegociationMiddleware $contentNegociationMiddleware,
        ContainerInterface $container,
        string $endpointName,
        ParameterInjecter $parameterInjecter
    )
    {
        $this->contentNegociationMiddleware = $contentNegociationMiddleware;
        $this->container = $container;
        $this->endpointName = $endpointName;
        $this->parameterInjecter = $parameterInjecter;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $endpoint = $this->container->get($this->endpointName);

        $endpointResponse = $this->parameterInjecter->injectParametersFromRequestAndReturnEndpointResponse($request, $endpoint);

        return $this->contentNegociationMiddleware->factoryHttpResponse($endpointResponse);
    }
}