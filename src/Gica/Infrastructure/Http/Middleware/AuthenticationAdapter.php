<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware;


use Psr\Http\Message\ServerRequestInterface;

interface AuthenticationAdapter
{
    public function getAuthenticatedUserId(ServerRequestInterface $request);
}