<?php
/**
 * Copyright (c) 2017 Constantin Galbenu <xprt64@gmail.com>
 */

namespace Gica\Infrastructure\Http\Lib;


use Psr\Http\Message\ServerRequestInterface;

class CacheHeadersLib
{
    public function factoryEtagFromDateForUrl(string $url, \DateTimeImmutable $lastModified)
    {
        return md5($url . $lastModified->format('c'));
    }

    public function formatClientDate(\DateTimeImmutable $lastModified)
    {
        return $lastModified->format('D, d M Y H:i:s T');
    }

    public function checkIfNoneMatchHeader(ServerRequestInterface $request, \DateTimeImmutable $serverLastModifiedDate)
    {
        if (!$request->hasHeader('If-None-Match')) {
            return false;
        }

        $clientETag = (string)$request->getHeader('If-None-Match')[0];
        $serverETag = $this->factoryEtagFromDateForUrl($request->getUri()->__toString(), $serverLastModifiedDate);

        return $serverETag === $clientETag;
    }

    public function checkIfModifiedSince(ServerRequestInterface $request, \DateTimeImmutable $serverLastModifiedDate)
    {
        if (!$request->hasHeader('If-Modified-Since')) {
            return false;
        }

        $clientLastModified = new \DateTimeImmutable($request->getHeader('If-Modified-Since')[0]);

        return $serverLastModifiedDate <= $clientLastModified;
    }
}