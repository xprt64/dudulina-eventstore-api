<?php
/**
 * @copyright  Copyright (c) Constantin Galbenu xprt64@gmail.com
 * All rights reserved.
 */

namespace Gica\Infrastructure\Http\Middleware\Authentication\AuthenticationAdapter;


use Firebase\JWT\JWT;
use Gica\Infrastructure\Http\Middleware\AuthenticationAdapter;
use Psr\Http\Message\ServerRequestInterface;

/**
 * It performs JWT authentication based on a token found as 'token' query parameter or 'Token' Header
 */
class JWTAdapter implements AuthenticationAdapter
{

    /**
     * @var string
     */
    private $secretKey;
    /**
     * @var string
     */
    private $algorithm;

    public function __construct(
        string $secretKey,
        string $algorithm = 'HS256'
    )
    {
        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
    }

    public function getAuthenticatedUserId(ServerRequestInterface $request)
    {
        $token = $this->getTokenFromRequest($request);

        if ($token) {
            $decodedToken = $this->decodeToken($token);

            if ($decodedToken) {
                return $decodedToken['userId'];
            }
        }

        return null;
    }


    private function decodeToken($token)
    {
        try {
            return (array)JWT::decode($token, $this->secretKey, [$this->algorithm]);

        } catch (\InvalidArgumentException $exception) {

        } catch (\UnexpectedValueException $exception) {
        }

        return false;
    }

    private function getTokenFromRequest(ServerRequestInterface $request)
    {
        $queryParams = $request->getQueryParams();

        if (isset($queryParams['token'])) {
            return $queryParams['token'];
        }

        $tokenHeaderLines = $request->getHeader('Token');

        if (!empty($tokenHeaderLines)) {
            return $tokenHeaderLines[0];
        }

        return null;
    }

}