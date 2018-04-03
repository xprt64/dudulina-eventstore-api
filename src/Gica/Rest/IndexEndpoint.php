<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Rest;


use Gica\Rest\Endpoint\EndpointResponse;
use Gica\Rest\Helper\AbsoluteUrlCreator;

class IndexEndpoint
{
    /**
     * @var AbsoluteUrlCreator
     */
    private $urlGenerator;

    public function __construct(
        AbsoluteUrlCreator $urlGenerator
    )
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke()
    {
        return new EndpointResponse(
            [
                'self'   => $this->urlGenerator->generateUri('route.index'),
                'events' => $this->urlGenerator->generateUri('route.events.list'),
                'docs'   => $this->urlGenerator->makeUriAbsolute('/api.raml'),
            ],
            200
        );
    }
}