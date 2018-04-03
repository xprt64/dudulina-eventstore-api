<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Dependency\Http;


use Gica\Infrastructure\Http\Middleware\Authentication\AuthenticationAdapter\JWTAdapter;
use Gica\Infrastructure\Http\Middleware\AuthenticationMiddleware;
use Psr\Container\ContainerInterface;

class AuthenticationMiddlewareFactory
{
    function __invoke(ContainerInterface $container)
    {
        $jwtConfig = $container->get('config')['auth']['jwt'];

        return new AuthenticationMiddleware(
            new JWTAdapter(
                $jwtConfig['secret'],
                $jwtConfig['algorithm']
            )
        );
    }
}