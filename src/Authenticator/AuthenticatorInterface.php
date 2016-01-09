<?php

namespace Slim\Authenticator;

use Psr\Http\Message\RequestInterface;

interface AuthenticatorInterface
{
    /**
     * @param RequestInterface $request
     */
    public function authenticate(RequestInterface $request);
}
