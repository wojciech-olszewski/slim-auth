<?php

namespace spec\Slim\Authenticator;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Slim\Authenticator\HttpBasic\UserArrayStrategy;
use Slim\Exception\UnauthorizedException;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;

class HttpBasicAuthenticatorSpec extends ObjectBehavior
{
    const TYPE_PHP_AUTH = 1;
    const TYPE_ENVIRONMENT = 2;

    public function it_is_initializable()
    {
        $this->shouldHaveType('Slim\Authenticator\HttpBasicAuthenticator');
        $this->shouldImplement('Slim\Authenticator\AuthenticatorInterface');
    }

    public function it_require_strategy_in_options()
    {
        $this->shouldThrow('\RuntimeException')->duringInstantiation();
    }

    public function it_require_strategy_interface_in_options()
    {
        $this->beConstructedWith([
            'strategy' => new \stdClass()
        ]);

        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_has_default_options($strategy)
    {
        $strategy->beADoubleOf('Slim\Authenticator\HttpBasic\StrategyInterface');
        $this->beConstructedWith([
            'strategy' => $strategy
        ]);

        $this->getEnvironment()->shouldReturn('HTTP_AUTHORIZATION');
        $this->getRealm()->shouldReturn('Restricted area');
    }

    public function it_require_environment_string_in_options($strategy)
    {
        $strategy->beADoubleOf('Slim\Authenticator\HttpBasic\StrategyInterface');
        $this->beConstructedWith([
            'strategy' => $strategy,
            'environment' => 123
        ]);

        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_require_realm_string_in_options($strategy)
    {
        $strategy->beADoubleOf('Slim\Authenticator\HttpBasic\StrategyInterface');
        $this->beConstructedWith([
            'strategy' => $strategy,
            'realm' => 123
        ]);

        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public function it_throw_exception_if_unable_to_resolve_credentials($strategy)
    {
        $strategy->beADoubleOf('Slim\Authenticator\HttpBasic\StrategyInterface');
        $this->beConstructedWith([
            'strategy' => $strategy
        ]);
        $request = $this->createRequest();

        $this->shouldThrow('\RuntimeException')->duringAuthenticate($request);
    }

    public function it_return_true_with_valid_credentials_via_php_auth_params()
    {
        $this->beConstructedWith([
            'strategy' => new UserArrayStrategy([
                'user' => 'password'
            ])
        ]);
        $request = $this->createRequest(
            [
                'user' => 'user',
                'password' => 'password',
            ],
            self::TYPE_PHP_AUTH
        );

        $this->authenticate($request)->shouldReturn(true);
    }

    public function it_return_true_with_valid_credentials_via_environment_params()
    {
        $this->beConstructedWith([
            'strategy' => new UserArrayStrategy([
                'user' => 'password'
            ])
        ]);
        $request = $this->createRequest(
            [
                'user' => 'user',
                'password' => 'password',
            ],
            self::TYPE_ENVIRONMENT
        );

        $this->authenticate($request)->shouldReturn(true);
    }

    public function it_throw_unauthorized_exception_with_invalid_credentials()
    {
        $this->beConstructedWith([
            'strategy' => new UserArrayStrategy([
                'user' => 'password'
            ])
        ]);
        $request = $this->createRequest(
            [
                'user' => 'user',
                'password' => 'wrong_password',
            ],
            self::TYPE_PHP_AUTH
        );

        $this->shouldThrow('\Slim\Exception\UnauthorizedException')->duringAuthenticate($request);
    }

    public function it_should_return_valid_response_on_unauthorized()
    {
        $this->beConstructedWith([
            'strategy' => new UserArrayStrategy([
                'user' => 'password'
            ])
        ]);

        $request = $this->createRequest(
            [
                'user' => 'user',
                'password' => 'password',
            ],
            self::TYPE_ENVIRONMENT
        );

        $response = new Response();
        $exception = new UnauthorizedException();

        $outputResponse = $this->onUnauthorized($request, $response, $exception);
        $outputResponse->shouldImplement('\Psr\Http\Message\ResponseInterface');
        $outputResponse->getStatusCode()->shouldReturn(401);
        $outputResponse->getHeader('WWW-Authenticate')->shouldReturn(['Basic realm="Restricted area"']);
    }

    /**
     * @param array $credentials
     * @return Request
     */
    private function createRequest($credentials = [], $type = self::TYPE_PHP_AUTH)
    {
        $serverParams = [];

        if ([] !== $credentials) {
            if (self::TYPE_PHP_AUTH === $type) {
                $serverParams['PHP_AUTH_USER'] = $credentials['user'];
                $serverParams['PHP_AUTH_PW'] = $credentials['password'];
            } else {
                $serverParams['HTTP_AUTHORIZATION'] = sprintf(
                    'Basic %s',
                    base64_encode(implode(':', $credentials))
                );
            }
        }

        return new Request(
            'GET',
            Uri::createFromString('http://example.org'),
            new Headers(),
            [],
            $serverParams,
            new RequestBody()
        );
    }
}
