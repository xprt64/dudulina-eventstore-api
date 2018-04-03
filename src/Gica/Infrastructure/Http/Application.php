<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http;


use Gica\Infrastructure\Http\Middleware\Delegate\NotFoundDelegate;
use Gica\Infrastructure\Http\Middleware\Factory;
use Gica\Infrastructure\Http\Middleware\Pipe;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmitterInterface;

/**
 * The main pipe
 */
class Application extends Pipe
{
    /**
     * @var EmitterInterface
     */
    private $emitter;
    /**
     * @var Factory
     */
    private $factory;

    public function __construct(
        Factory $factory,
        EmitterInterface $emitter
    )
    {
        parent::__construct($factory);
        $this->emitter = $emitter;
        $this->factory = $factory;
    }

    public function processPipeAndEmitResponse(ServerRequestInterface $serverRequest)
    {
        $response = $this->process($serverRequest, new NotFoundDelegate());

        $this->emitter->emit($response);
    }
}