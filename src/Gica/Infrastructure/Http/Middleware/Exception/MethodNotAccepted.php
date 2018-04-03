<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware\Exception;


class MethodNotAccepted extends \Exception
{
    /**
     * @var array
     */
    private $allowedMethods;

    public function __construct($method, array $allowedMethods)
    {
        $this->allowedMethods = $allowedMethods;

        parent::__construct(sprintf("Method %s not allowed, only these are permitted %s", htmlentities($method), implode(',', $allowedMethods)), 405);
    }

    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}