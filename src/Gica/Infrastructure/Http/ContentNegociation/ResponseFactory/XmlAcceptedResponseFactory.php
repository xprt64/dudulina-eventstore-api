<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\ContentNegociation\ResponseFactory;


use Gica\Infrastructure\Http\ContentNegociation\ResponseFactory;
use Gica\Rest\Endpoint\EndpointResponse;
use Gica\Infrastructure\Http\Lib\ArrayToXmlConverter;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

class XmlAcceptedResponseFactory implements ResponseFactory
{
    /**
     * @var ArrayToXmlConverter
     */
    private $converter;

    public function __construct(
        ArrayToXmlConverter $converter
    )
    {
        $this->converter = $converter;
    }

    public function match($acceptHeader): bool
    {
        if (!is_array($acceptHeader)) {
            $acceptHeader = [$acceptHeader];
        }

        foreach ($acceptHeader as $item) {
            if (1 === preg_match('#^application/([^+\s]+\+)?xml#', $item)) {
                return true;
            }
        }

        return false;
    }

    public function convertToResponse(?EndpointResponse $endpointResponse): ResponseInterface
    {
        $body = new Stream('php://temp', 'wb+');
        if ($endpointResponse) {
            $body->write($this->converter->convert($endpointResponse->getData(), 'result'));
            $body->rewind();
        }

        return new Response($body, 200, ['content-type' => 'application/xml']);
    }

}