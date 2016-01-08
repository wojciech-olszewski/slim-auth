<?php

namespace spec\Slim\Rule;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Uri;

class MethodRuleSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Slim\Rule\MethodRule');
        $this->shouldImplement('Slim\Rule\RuleInterface');
    }

    public function it_has_default_values()
    {
        $this->getMethods()->shouldReturn([
            'OPTIONS',
            'GET',
            'HEAD',
            'POST',
            'PUT',
            'DELETE',
        ]);

        $this->getExcluded()->shouldReturn([]);
    }

    public function it_allows_to_pass_options_by_constructor()
    {
        $this->beConstructedWith([
            'methods' => ['GET', 'POST'],
            'excluded' => ['OPTIONS']
        ]);

        $this->getMethods()->shouldReturn(['GET', 'POST']);
        $this->getExcluded()->shouldReturn(['OPTIONS']);
    }

    public function it_normalizes_methods_to_upper()
    {
        $this->beConstructedWith([
            'methods' => ['get', 'post'],
            'excluded' => ['options']
        ]);

        $this->getMethods()->shouldReturn(['GET', 'POST']);
        $this->getExcluded()->shouldReturn(['OPTIONS']);
    }

    public function it_returns_true_when_methods_contains_request_method()
    {
        $this->beConstructedWith([
            'methods' => ['GET', 'POST'],
        ]);
        $request = $this->createRequest('GET');

        $this->handle($request)->shouldReturn(true);
    }

    public function it_returns_false_when_methods_do_not_contains_request_method()
    {
        $this->beConstructedWith([
            'methods' => ['GET', 'POST'],
        ]);
        $request = $this->createRequest('OPTIONS');

        $this->handle($request)->shouldReturn(false);
    }

    public function it_return_false_when_excluded_contains_request_method()
    {
        $this->beConstructedWith([
            'excluded' => ['OPTIONS'],
        ]);
        $request = $this->createRequest('OPTIONS');

        $this->handle($request)->shouldReturn(false);
    }

    /**
     * @param string $method
     * @return Request
     */
    private function createRequest($method)
    {
        return new Request(
            $method,
            Uri::createFromString('http://example.org'),
            new Headers(),
            [],
            [],
            new RequestBody()
        );
    }
}
