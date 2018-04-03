<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Rest\Cache;


use Psr\Http\Message\ServerRequestInterface;

/**
 * Dependency Inversion Principle applied: this interface is owned by the UI and implemented by the Infrastructure
 */
interface CacheableByDateEndpoint
{
    public function getLastModifiedDate(ServerRequestInterface $request):?\DateTimeImmutable;
}