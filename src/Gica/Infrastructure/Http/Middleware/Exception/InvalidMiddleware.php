<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware\Exception;


class InvalidMiddleware extends \InvalidArgumentException
{
    private $middleware;

    public function __construct(
        $middleware
    )
    {
        $this->middleware = $middleware;

        parent::__construct("Middleware is not valid");
    }

    public function getMiddleware()
    {
        return $this->middleware;
    }
}