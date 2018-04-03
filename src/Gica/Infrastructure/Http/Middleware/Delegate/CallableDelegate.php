<?php


namespace Gica\Infrastructure\Http\Middleware\Delegate;


use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableDelegate implements DelegateInterface
{
    /**
     * @var
     */
    private $method;

    public function __construct(
        $method
    )
    {
        $this->method = $method;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request)
    {
        return call_user_func($this->method, $request);
    }
}