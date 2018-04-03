<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Dependency\Http;

use Gica\Infrastructure\Http\ContentNegociation\ResponseFactory\JsonAcceptedResponseFactory;
use Gica\Infrastructure\Http\ContentNegociation\ResponseFactory\XmlAcceptedResponseFactory;
use Gica\Infrastructure\Http\ContentNegociation\ResponseNegociator\CompositeResponseNegociator;
use Gica\Infrastructure\Http\Lib\ArrayToXmlConverter;
use Psr\Container\ContainerInterface;

class ResponseNegociatorFactory
{
    function __invoke(ContainerInterface $container)
    {
        return new CompositeResponseNegociator(
            new JsonAcceptedResponseFactory()
        );
    }
}