<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\Middleware\RouterMiddleware\RouterInterface;
use Gica\Rest\Helper\AbsoluteUrlCreator;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware that help building absolute URIs
 */
class AbsoluteUrlsMiddleware implements MiddlewareInterface, AbsoluteUrlCreator
{
    /** @var string */
    private $prepend;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        RouterInterface $router
    )
    {
        $this->router = $router;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $this->prepend = $request->getUri()->getScheme() . '://' . $request->getUri()->getAuthority();

        return $delegate->process($request);
    }

    public function generateUri($name, array $parameters = [])
    {
        return $this->makeUriAbsolute($this->router->generateUri($name, $parameters));
    }

    public function makeUriAbsolute($uri)
    {
        return $this->prepend . $uri;
    }
}