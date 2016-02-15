<?php

namespace spec\Slim\Authenticator;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Uri;

class JsonWebTokenAuthenticatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Slim\Authenticator\JsonWebTokenAuthenticator');
        $this->shouldImplement('Slim\Authenticator\AuthenticatorInterface');
    }

    public function it_require_secret_in_options()
    {
        $this
            ->shouldThrow(new \RuntimeException('Option "secret" is required'))
            ->duringInstantiation();
    }

    public function it_require_secret_string_in_options()
    {
        $this->beConstructedWith([
            'secret' => new \stdClass()
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Option "secret" should be string, object given'))
            ->duringInstantiation();
    }

    public function it_require_signer_in_options()
    {
        $this->beConstructedWith([
            'secret' => 'loremipsumdolor',
        ]);

        $this
            ->shouldThrow(new \RuntimeException('Option "signer" is required'))
            ->duringInstantiation();
    }

    public function it_require_signer_interface_in_options()
    {
        $this->beConstructedWith([
            'secret' => 'loremipsumdolor',
            'signer' => new \stdClass()
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Option "signer" should be Lcobucci\JWT\Signer, stdClass given'))
            ->duringInstantiation();
    }

    public function it_require_environment_string_in_options(Signer $signer)
    {
        $this->beConstructedWith([
            'secret' => 'loremipsumdolor',
            'signer' => $signer,
            'environment' => 123
        ]);

        $this
            ->shouldThrow(new \InvalidArgumentException('Option "environment" should be string, integer given'))
            ->duringInstantiation();
    }

    public function it_throw_exception_if_unable_to_resolve_token(Signer $signer)
    {
        $this->beConstructedWith([
            'secret' => 'loremipsumdolor',
            'signer' => $signer,
        ]);
        $request = $this->createRequest();

        $this
            ->shouldThrow(new \RuntimeException('Unable to resolve token'))
            ->duringAuthenticate($request);
    }

    public function it_throw_exception_if_unable_to_authenticate(Signer $signer)
    {
        $this->beConstructedWith([
            'secret' => 'loremipsumdolor',
            'signer' => $signer,
        ]);
        $request = $this->createRequest('invalid_token');

        $this
            ->shouldThrow(new \RuntimeException('Unable to authenticate'))
            ->duringAuthenticate($request);
    }

    public function it_return_decoded_token_with_valid_token_string()
    {
        $signer = new Sha256();
        $this->beConstructedWith([
            'secret' => 'loremipsumdolor',
            'signer' => $signer,
        ]);

        $token = (new Builder())
            ->sign($signer, 'loremipsumdolor')
            ->getToken();

        $request = $this->createRequest((string) $token);

        $this->authenticate($request)->shouldHaveType('Lcobucci\JWT\Token');
    }

    /**
     * @param string|null $token
     * @return Request
     */
    private function createRequest($token = null)
    {
        $serverParams = [];

        if (null !== $token) {
            $serverParams['HTTP_AUTHORIZATION'] = sprintf(
                'Bearer %s',
                $token
            );
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
