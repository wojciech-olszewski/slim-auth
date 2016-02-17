<?php

namespace spec\Slim;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Authenticator\AuthenticatorInterface;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Uri;
use Slim\Rule\PathRule;

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

    public function it_is_invocable(
        AuthenticatorInterface $authenticator,
        ResponseInterface $response
    ) {
        $this->beConstructedWith([
            'authenticator' => $authenticator
        ]);
        $request = $this->createRequest();
        $next = function (RequestInterface $request, ResponseInterface $response) {
            return $response;
        };

        $this($request, $response, $next)->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }

    public function it_require_authenticator_in_options()
    {
        $this
            ->shouldThrow(new \RuntimeException('Option "authenticator" is required'))
            ->duringInstantiation();
    }

    public function it_require_authenticator_interface_in_options()
    {
        $this->beConstructedWith([
            'authenticator' => new \stdClass()
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Option "authenticator" should be instance of Slim\Authenticator\AuthenticatorInterface, stdClass given'))
            ->duringInstantiation();
    }

    public function it_require_rule_interfaces_in_options(AuthenticatorInterface $authenticator)
    {
        $this->beConstructedWith([
            'authenticator' => $authenticator,
            'rules' => [
                new \stdClass()
            ]
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Each option in "rules" array should be instance of Slim\Rule\RuleInterface, stdClass given'))
            ->duringInstantiation();
    }

    public function it_has_default_rules(AuthenticatorInterface $authenticator)
    {
        $this->beConstructedWith([
            'authenticator' => $authenticator
        ]);

        $rules = $this->getRules();

        $rules->shouldBeArray();
        $rules->shouldContainsInstanceOf('Slim\Rule\PathRule');
        $rules->shouldContainsInstanceOf('Slim\Rule\MethodRule');
    }

    public function it_require_callable_on_unauthorized_callback(AuthenticatorInterface $authenticator)
    {
        $this->beConstructedWith([
            'authenticator' => $authenticator,
            'onUnauthorized' => new \stdClass()
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Option "onUnauthorized" should be callable, object given'))
            ->duringInstantiation();
    }

    public function it_require_callable_on_success_callback(AuthenticatorInterface $authenticator)
    {
        $this->beConstructedWith([
            'authenticator' => $authenticator,
            'onSuccess' => new \stdClass()
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Option "onSuccess" should be callable, object given'))
            ->duringInstantiation();
    }

    public function it_call_authenticator_when_rules_handle_request(
        AuthenticatorInterface $authenticator,
        ResponseInterface $response
    ) {
        $this->beConstructedWith([
            'authenticator' => $authenticator,
        ]);
        $request = $this->createRequest();
        $authenticator->authenticate($request)->shouldBeCalled();
        $next = function (RequestInterface $request, ResponseInterface $response) {
            return $response;
        };

        $this($request, $response, $next)->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }

    public function it_does_not_call_authenticator_when_rules_do_not_handle_request(
        AuthenticatorInterface $authenticator,
        ResponseInterface $response
    ) {
        $this->beConstructedWith([
            'authenticator' => $authenticator,
            'rules' => [
                new PathRule([
                    'excluded' => ['/']
                ])
            ]
        ]);
        $request = $this->createRequest();
        $authenticator->authenticate($request)->shouldNotBeCalled();
        $next = function (RequestInterface $request, ResponseInterface $response) {
            return $response;
        };

        $this($request, $response, $next)->shouldReturnAnInstanceOf('Psr\Http\Message\ResponseInterface');
    }

    public function getMatchers()
    {
        return [
            'containsInstanceOf' => function ($subject, $interface) {
                return [] !== array_filter($subject, function ($item) use ($interface) {
                    return $item instanceof $interface;
                });
            },
        ];
    }

    /**
     * @return Request
     */
    private function createRequest()
    {
        return new Request(
            'GET',
            Uri::createFromString('http://example.org'),
            new Headers(),
            [],
            [],
            new RequestBody()
        );
    }
}
