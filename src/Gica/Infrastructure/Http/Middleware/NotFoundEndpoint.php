<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Rest\Endpoint\EndpointResponse;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundEndpoint implements MiddlewareInterface
{
    /**
     * @var ContentNegociationMiddleware
     */
    private $contentNegociationMiddleware;

    public function __construct(
        ContentNegociationMiddleware $contentNegociationMiddleware
    )
    {
        $this->contentNegociationMiddleware = $contentNegociationMiddleware;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $this->contentNegociationMiddleware->factoryHttpResponse(new EndpointResponse(
            ['message' => 'Endpoint not found'],
            404));
    }
}