<?php

namespace Slim\Authenticator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\UnauthorizedException;

interface AuthenticatorInterface
{
    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function authenticate(RequestInterface $request);

    /**
     * @param RequestInterface      $request
     * @param ResponseInterface     $response
     * @param UnauthorizedException $e
     * @return ResponseInterface
     */
    public function onUnauthorized(RequestInterface $request, ResponseInterface $response, UnauthorizedException $e);
}
