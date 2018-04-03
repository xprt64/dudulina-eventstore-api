<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Rest\Helper;


interface AbsoluteUrlCreator
{

    public function generateUri($name, array $parameters = []);

    public function makeUriAbsolute($uri);
}