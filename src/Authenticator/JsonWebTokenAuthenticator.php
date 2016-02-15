<?php

namespace Slim\Authenticator;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\UnauthorizedException;

class JsonWebTokenAuthenticator implements AuthenticatorInterface
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var string
     */
    private $environment = 'HTTP_AUTHORIZATION';

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @inheritdoc
     */
    public function authenticate(RequestInterface $request)
    {
        $token = $this->resolveToken($request);

        try {
            return $this->decodeToken($token);
        } catch (UnauthorizedException $e) {
            throw new UnauthorizedException('Unable to authenticate', null, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function onUnauthorized(RequestInterface $request, ResponseInterface $response, UnauthorizedException $e)
    {
        return $response->withStatus(401);
    }

    /**
     * @param array $options
     */
    private function setOptions(array $options)
    {
        $this->setSecret($options);
        $this->setSigner($options);
        $this->setEnvironment($options);
    }

    /**
     * @param array $options
     */
    private function setSecret(array $options)
    {
        if (!array_key_exists('secret', $options)) {
            throw new \RuntimeException('Option "secret" is required');
        }

        if (!is_string($options['secret'])) {
            throw new \InvalidArgumentException(sprintf(
                'Option "secret" should be string, %s given',
                gettype($options['secret'])
            ));
        }

        $this->secret = $options['secret'];
    }

    /**
     * @param array $options
     */
    private function setSigner(array $options)
    {
        if (!array_key_exists('signer', $options)) {
            throw new \RuntimeException('Option "signer" is required');
        }

        if (!$options['signer'] instanceof Signer) {
            throw new \InvalidArgumentException(sprintf(
                'Option "signer" should be Lcobucci\JWT\Signer, %s given',
                get_class($options['signer'])
            ));
        }

        $this->signer = $options['signer'];
    }

    /**
     * @param array $options
     */
    private function setEnvironment(array $options)
    {
        if (!array_key_exists('environment', $options)) {
            return;
        }

        if (!is_string($options['environment'])) {
            throw new \InvalidArgumentException(sprintf(
                'Option "environment" should be string, %s given',
                gettype($options['environment'])
            ));
        }

        $this->environment = $options['environment'];
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    private function resolveToken(RequestInterface $request)
    {
        $serverParams = $request->getServerParams();

        if (!array_key_exists($this->environment, $serverParams)) {
            throw new \RuntimeException('Unable to resolve token');
        }

        return substr($serverParams[$this->environment], strlen('Bearer '));
    }

    /**
     * @param $token
     * @return Token
     */
    private function decodeToken($token)
    {
        try {
            $decodedToken = (new Parser())->parse($token);

            if (false === $this->isVerified($decodedToken)) {
                throw new UnauthorizedException('Unable to verify token');
            }

            return $decodedToken;
        } catch (\Exception $e) {
            throw new UnauthorizedException(sprintf(
                'Unable to decode token %s',
                $token
            ));
        }
    }

    /**
     * @param Token $token
     * @return bool
     */
    private function isVerified(Token $token)
    {
        return $token->verify($this->signer, $this->secret);
    }
}
