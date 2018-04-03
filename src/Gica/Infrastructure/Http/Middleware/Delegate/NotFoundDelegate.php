<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware\Delegate;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotFoundDelegate implements DelegateInterface
{
    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request)
    {
        throw new \RuntimeException("Add a 'not found handler' to main pipeline", 500);
    }
}