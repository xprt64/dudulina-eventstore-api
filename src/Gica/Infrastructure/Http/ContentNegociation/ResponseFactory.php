<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\ContentNegociation;


use Gica\Rest\Endpoint\EndpointResponse;
use Psr\Http\Message\ResponseInterface;

interface ResponseFactory
{
    public function match($acceptHeader): bool;

    public function convertToResponse(?EndpointResponse $endpointResponse): ResponseInterface;
}