<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Rest\Endpoint;

/**
 * An endpoint's response.
 */
class EndpointResponse
{
    private $data;
    /**
     * @var int
     */
    private $statusCode;
    /**
     * @var array
     */
    private $headers;

    public function __construct($data, int $statusCode = 200, array $headers = [])
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function withAddedHeader($name, $value): self
    {
        $other = clone $this;
        $other->headers[$name] = $value;
        return $other;
    }
}