<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\ContentNegociation;


interface ResponseNegociator
{
    public function factoryResponseByAcceptHeader($acceptHeader):ResponseFactory;
}