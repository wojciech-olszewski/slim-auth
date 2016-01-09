<?php

namespace spec\Slim\Authenticator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HttpBasicAuthenticatorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Slim\Authenticator\HttpBasicAuthenticator');
        $this->shouldImplement('Slim\Authenticator\AuthenticatorInterface');
    }
}
