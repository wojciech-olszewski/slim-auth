<?php

namespace spec\Slim;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SlimAuthSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Slim\SlimAuth');
    }

    function it_is_invocable($request, $response)
    {
        $request->beADoubleOf('Psr\Http\Message\RequestInterface');
        $response->beADoubleOf('Psr\Http\Message\ResponseInterface');
        $next = function (RequestInterface $request, ResponseInterface $response) {
            return $response;
        };

        $this($request, $response, $next)->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }
}
