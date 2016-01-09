<?php

namespace Slim\Authenticator;

use Psr\Http\Message\RequestInterface;

class HttpBasicAuthenticator implements AuthenticatorInterface
{
    /**
     * @inheritdoc
     */
    public function authenticate(RequestInterface $request)
    {

    }
}
