<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\ContentNegociation\ResponseNegociator;


use Gica\Infrastructure\Http\ContentNegociation\ResponseNegociator;
use Gica\Infrastructure\Http\ContentNegociation\ResponseNegociator\Exception\NoAcceptResponseFactoryFound;
use Gica\Infrastructure\Http\ContentNegociation\ResponseFactory;

class CompositeResponseNegociator implements ResponseNegociator
{

    /**
     * @var ResponseFactory[]
     */
    private $responseFactories;

    public function __construct(ResponseFactory ...$responseFactories)
    {
        $this->responseFactories = $responseFactories;
    }

    public function factoryResponseByAcceptHeader($acceptHeader):ResponseFactory
    {
        foreach($this->responseFactories as $responseFactory)
        {
            if( $responseFactory->match($acceptHeader) )
            {
                return $responseFactory;
            }
        }

        throw new NoAcceptResponseFactoryFound();
    }
}