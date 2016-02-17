<?php

namespace Slim\Authenticator;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Authenticator\HttpBasic\StrategyInterface;
use Slim\Exception\UnauthorizedException;

class HttpBasicAuthenticator implements AuthenticatorInterface
{
    /**
     * @var StrategyInterface
     */
    private $strategy;

    /**
     * @var string
     */
    private $environment = 'HTTP_AUTHORIZATION';

    /**
     * @var string
     */
    private $realm = 'Restricted area';

    /**
     * @var callable[]
     */
    private $credentialsResolvers;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
        $this->setupResolvers();
    }

    /**
     * @inheritdoc
     */
    public function authenticate(RequestInterface $request)
    {
        list($user, $password) = $this->resolveCredentials($request);

        try {
            $this->strategy->authenticate($user, $password);

            return $user;
        } catch (UnauthorizedException $e) {
            throw new UnauthorizedException('Unable to authenticate', null, $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function onUnauthorized(RequestInterface $request, ResponseInterface $response, UnauthorizedException $e)
    {
        return $response
            ->withStatus(401)
            ->withHeader('WWW-Authenticate', sprintf('Basic realm="%s"', $this->realm));
    }

    /**
     * @return StrategyInterface
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * @param array $options
     */
    private function setOptions(array $options)
    {
        $this->setStrategy($options);
        $this->setRealm($options);
        $this->setEnvironment($options);
    }

    /**
     * @param array $options
     */
    private function setStrategy(array $options)
    {
        if (!array_key_exists('strategy', $options)) {
            throw new \RuntimeException('Option "strategy" is required');
        }

        if (!$options['strategy'] instanceof StrategyInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Option "strategy" should be instance of Slim\Authenticator\HttpBasic\StrategyInterface, %s given',
                get_class($options['strategy'])
            ));
        }

        $this->strategy = $options['strategy'];
    }

    /**
     * @param array $options
     */
    private function setRealm(array $options)
    {
        if (!array_key_exists('realm', $options)) {
            return;
        }

        if (!is_string($options['realm'])) {
            throw new \InvalidArgumentException(sprintf(
                'Option "realm" should be string, %s given',
                gettype($options['realm'])
            ));
        }

        $this->realm = $options['realm'];
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

    private function setupResolvers()
    {
        $phpAuthParamsResolver = function ($serverParams) {
            if (!array_key_exists('PHP_AUTH_USER', $serverParams) || !array_key_exists('PHP_AUTH_PW', $serverParams)) {
                throw new \RuntimeException('Both params PHP_AUTH_USER and PHP_AUTH_PW should be set');
            }

            return [
                $serverParams['PHP_AUTH_USER'],
                $serverParams['PHP_AUTH_PW']
            ];
        };

        $environmentAuthParamsResolver = function ($serverParams) {
            if (!array_key_exists($this->environment, $serverParams)) {
                throw new \RuntimeException(sprintf(
                    'Param %s is not set',
                    $this->environment
                ));
            }

            return explode(':', base64_decode(substr($serverParams[$this->environment], strlen('Basic '))));
        };

        $this->credentialsResolvers = [
            $phpAuthParamsResolver,
            $environmentAuthParamsResolver
        ];
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    private function resolveCredentials(RequestInterface $request)
    {
        $serverParams = $request->getServerParams();
        $lastException = null;

        foreach ($this->credentialsResolvers as $resolver) {
            try {
                return $resolver($serverParams);
            } catch (\Exception $e) {
                $lastException = $e;
            }
        }

        throw new \RuntimeException('Unable to resolve credentials', null, $lastException);
    }
}
