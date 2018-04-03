<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Infrastructure\Dependency\Http;


use Gica\Infrastructure\Http\Middleware\AbsoluteUrlsMiddleware;
use Psr\Container\ContainerInterface;

class AbsoluteUrlCreatorFactory
{
    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return $container->get(AbsoluteUrlsMiddleware::class);
    }
}