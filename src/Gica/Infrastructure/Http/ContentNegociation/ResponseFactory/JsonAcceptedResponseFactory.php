<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\ContentNegociation\ResponseFactory;


use Gica\Infrastructure\Http\ContentNegociation\ResponseFactory;
use Gica\Rest\Endpoint\EndpointResponse;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\Response\JsonResponse;

class JsonAcceptedResponseFactory implements ResponseFactory
{
    public function match($acceptHeader): bool
    {
        if (!is_array($acceptHeader)) {
            $acceptHeader = [$acceptHeader];
        }

        foreach ($acceptHeader as $item) {
            if (1 === preg_match('#^application/([^+\s]+\+)?json#', $item) || 1 === preg_match('#\*#', $item)) {
                return true;
            }
        }

        return false;
    }

    public function convertToResponse(?EndpointResponse $endpointResponse): ResponseInterface
    {
        if (null === $endpointResponse) {
            $response = new EmptyResponse();
        } else {
            $response = (new JsonResponse($endpointResponse->getData()));
        }
        return $response->withHeader('content-type', 'application/json');
    }
}