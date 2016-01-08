<?php

namespace spec\Slim;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SlimAuthSpec extends ObjectBehavior
{
    public function it_is_initializable($authenticator)
    {
        $authenticator->beADoubleOf('Slim\Authenticator\AuthenticatorInterface');
        $this->beConstructedWith([
            'authenticator' => $authenticator
        ]);

        $this->shouldHaveType('Slim\SlimAuth');
    }

    public function it_is_invocable($authenticator, $request, $response)
    {
        $authenticator->beADoubleOf('Slim\Authenticator\AuthenticatorInterface');
        $this->beConstructedWith([
            'authenticator' => $authenticator
        ]);
        $request->beADoubleOf('Psr\Http\Message\RequestInterface');
        $response->beADoubleOf('Psr\Http\Message\ResponseInterface');
        $next = function (RequestInterface $request, ResponseInterface $response) {
            return $response;
        };

        $this($request, $response, $next)->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }

    public function it_require_authenticator_in_options()
    {
        $this->shouldThrow('\RuntimeException')->duringInstantiation();;
    }

    public function it_require_authenticator_interface_in_options()
    {
        $this->beConstructedWith([
            'authenticator' => new \stdClass()
        ]);

        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_require_rule_interfaces_in_options($authenticator)
    {
        $authenticator->beADoubleOf('Slim\Authenticator\AuthenticatorInterface');
        $this->beConstructedWith([
            'authenticator' => $authenticator,
            'rules' => [
                new \stdClass()
            ]
        ]);

        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_has_default_options($authenticator)
    {
        $authenticator->beADoubleOf('Slim\Authenticator\AuthenticatorInterface');
        $this->beConstructedWith([
            'authenticator' => $authenticator
        ]);

        $rules = $this->getRules();

        $rules->shouldBeArray();
        $rules->shouldContainsInstanceOf('Slim\Rule\PathRule');
        $rules->shouldContainsInstanceOf('Slim\Rule\MethodRule');
    }

    public function getMatchers()
    {
        return [
            'containsInstanceOf' => function ($subject, $interface) {
                return [] !== array_filter($subject, function ($item) use ($interface) {
                    return is_a($item, $interface);
                });
            },
        ];
    }
}
