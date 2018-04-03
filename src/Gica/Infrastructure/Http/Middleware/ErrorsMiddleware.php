<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Rest\Endpoint\EndpointResponse;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles errors thrown down the pipeline and returns a negotiated response to client
 */
class ErrorsMiddleware implements MiddlewareInterface
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
        try {
            return $delegate->process($request);
        } catch (\Exception $exception) {
            return $this->contentNegociationMiddleware->factoryHttpResponse(new EndpointResponse(
                [
                    'message' => $exception->getMessage(),
                    'code'    => $exception->getCode(),
                ],
                $this->getStatusCode($exception)));
        }
    }

    protected function getStatusCode(\Exception $exception): int
    {
        return ($exception->getCode() >= 400 && $exception->getCode() < 600) ? $exception->getCode() : 500;
    }
}