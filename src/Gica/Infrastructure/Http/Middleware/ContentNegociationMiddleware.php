<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\ContentNegociation\ResponseFactory;
use Gica\Infrastructure\Http\ContentNegociation\ResponseFactory\JsonAcceptedResponseFactory;
use Gica\Infrastructure\Http\ContentNegociation\ResponseNegociator;
use Gica\Infrastructure\Http\ContentNegociation\ResponseNegociator\Exception\NoAcceptResponseFactoryFound;
use Gica\Rest\Endpoint\EndpointResponse;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Middleware used to transform the endpoint's response to a proper, negociated, HTTP response (i.e. JSON or XML)
 */
class ContentNegociationMiddleware implements MiddlewareInterface
{
    /**
     * @var ResponseNegociator
     */
    private $responseNegociator;

    /** @var ResponseFactory|null */
    private $responseFactory;

    public function __construct(ResponseNegociator $responseNegociator)
    {
        $this->responseNegociator = $responseNegociator;
    }

    public function factoryHttpResponse(?EndpointResponse $endpointResponse): ResponseInterface
    {
        if (!$this->responseFactory) {
            throw new \Exception("Pipe " . static::class . " middleware first");
        }

        $response = $this->responseFactory
            ->convertToResponse($endpointResponse);

        if ($endpointResponse) {
            $response = $response->withStatus($endpointResponse->getStatusCode());

            foreach ($endpointResponse->getHeaders() as $key => $value) {
                $response = $response->withAddedHeader($key, $value);
            }
        }

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        try {
            $this->responseFactory = $this->findResponseFactory($request);

            return $delegate->process($request);
        } catch (NoAcceptResponseFactoryFound $exception) {
            return new HtmlResponse('Requested content type not accepted', 406);
        }
    }

    private function findResponseFactory(ServerRequestInterface $request): ResponseFactory
    {
        $acceptHeader = $request->getHeader('accept');

        if (empty($acceptHeader)) {
            return new JsonAcceptedResponseFactory();
        }

        return $this->responseNegociator->factoryResponseByAcceptHeader(explode(',', reset($acceptHeader)));
    }
}