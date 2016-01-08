<?php

namespace spec\Slim\Rule;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Uri;

class PathRuleSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Slim\Rule\PathRule');
        $this->shouldImplement('Slim\Rule\RuleInterface');
    }

    public function it_has_default_values()
    {
        $this->getPath()->shouldReturn('/');
        $this->getExcluded()->shouldReturn([]);
    }

    public function it_allows_to_pass_excluded_as_string()
    {
        $this->beConstructedWith([
            'excluded' => '/excluded_path'
        ]);

        $this->getExcluded()->shouldReturn(['/excluded_path']);
    }

    public function it_allows_to_pass_excluded_as_array()
    {
        $this->beConstructedWith([
            'excluded' => ['/excluded_path_1', '/excluded_path_2']
        ]);

        $this->getExcluded()->shouldReturn(['/excluded_path_1', '/excluded_path_2']);
    }

    public function it_normalizes_paths_with_empty_path()
    {
        $this->beConstructedWith([
            'path' => '',
            'excluded' => ['']
        ]);

        $this->getPath()->shouldReturn('/');
        $this->getExcluded()->shouldReturn(['/']);
    }

    public function it_normalizes_paths_with_slash_only_path()
    {
        $this->beConstructedWith([
            'path' => '/',
            'excluded' => ['/']
        ]);

        $this->getPath()->shouldReturn('/');
        $this->getExcluded()->shouldReturn(['/']);
    }

    public function it_normalizes_paths_with_slash_at_the_beginning_path()
    {
        $this->beConstructedWith([
            'path' => '/path', 'excluded' => ['/excluded_path_1', '/excluded_path_2']
        ]);

        $this->getPath()->shouldReturn('/path');
        $this->getExcluded()->shouldReturn(['/excluded_path_1', '/excluded_path_2']);
    }

    public function it_normalizes_paths_with_slash_at_the_end_path()
    {
        $this->beConstructedWith([
            'path' => 'path/',
            'excluded' => ['excluded_path_1/', 'excluded_path_2/']
        ]);

        $this->getPath()->shouldReturn('/path');
        $this->getExcluded()->shouldReturn(['/excluded_path_1', '/excluded_path_2']);
    }

    public function it_normalizes_paths_with_slash_at_the_beginning_and_slash_at_the_end_path()
    {
        $this->beConstructedWith([
            'path' => '/path/',
            'excluded' => ['/excluded_path_1/', '/excluded_path_2/']
        ]);

        $this->getPath()->shouldReturn('/path');
        $this->getExcluded()->shouldReturn(['/excluded_path_1', '/excluded_path_2']);
    }

    public function it_return_true_when_uri_path_equals_path()
    {
        $request = $this->createRequest('http://example.org/api');
        $this->beConstructedWith([
            'path' => '/api'
        ]);

        $this->handle($request)->shouldReturn(true);
    }

    public function it_return_true_when_uri_path_contains_path()
    {
        $this->beConstructedWith([
            'path' => '/api'
        ]);
        $request = $this->createRequest('http://example.org/api/secured');

        $this->handle($request)->shouldReturn(true);
    }

    public function it_return_false_when_path_overlap_uri_path()
    {
        $this->beConstructedWith([
            'path' => '/api/secured'
        ]);
        $request = $this->createRequest('http://example.org/api');

        $this->handle($request)->shouldReturn(false);
    }

    public function it_return_false_when_path_equals_excluded()
    {
        $this->beConstructedWith([
            'path' => '/api',
            'excluded' => ['/api']
        ]);
        $request = $this->createRequest('http://example.org/api');

        $this->handle($request)->shouldReturn(false);
    }

    public function it_return_false_when_uri_path_contains_excluded()
    {
        $this->beConstructedWith([
            'path' => '/api', 'excluded' => ['/api/excluded']
        ]);
        $request = $this->createRequest('http://example.org/api/excluded/endpoint');

        $this->handle($request)->shouldReturn(false);
    }

    public function it_return_true_when_excluded_overlap_uri_path()
    {
        $this->beConstructedWith([
            'path' => '/api',
            'excluded' => ['/api/excluded']
        ]);
        $request = $this->createRequest('http://example.org/api');

        $this->handle($request)->shouldReturn(true);
    }

    /**
     * @param string $url
     * @return Request
     */
    private function createRequest($url)
    {
        return new Request(
            'GET',
            Uri::createFromString($url),
            new Headers(),
            [],
            [],
            new RequestBody()
        );
    }
}
