<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Gica\Infrastructure\Http\Middleware\Authentication\Exception\AuthenticationRequired;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Middleware used to authenticate and authorize users prior to accessing a delegate.
 * Authorization could be extracted into its own service.
 */
class AuthenticationMiddleware implements MiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if ($this->isAuthorized($request)) {
            return $delegate->process($request);
        }

        throw new AuthenticationRequired("You must login to access this page", 403);
    }

    private function isAuthorized(ServerRequestInterface $request)
    {
        return true;//logic to reject unauthenticated users
    }
}