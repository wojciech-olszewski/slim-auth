<?php

namespace Slim;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Authenticator\AuthenticatorInterface;
use Slim\Exception\UnauthorizedException;
use Slim\Rule\MethodRule;
use Slim\Rule\PathRule;
use Slim\Rule\RuleInterface;

class SlimAuth
{
    /**
     * @var AuthenticatorInterface
     */
    private $authenticator;

    /**
     * @var RuleInterface[]
     */
    private $rules = [];

    /**
     * @var callable
     */
    private $onUnauthorizedCallback;

    /**
     * @var callable
     */
    private $onSuccessCallback;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param callable          $next
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        if (!$this->isAuthenticable($request)) {
            return $next($request, $response);
        }

        try {
            $data = $this->authenticator->authenticate($request);
        } catch (UnauthorizedException $e) {
            return $this->onUnauthorized($request, $response, $e);
        }

        $this->onSuccess($request, $response, $data);

        return $next($request, $response);
    }

    /**
     * @return AuthenticatorInterface
     */
    public function getAuthenticator()
    {
        return $this->authenticator;
    }

    /**
     * @return RuleInterface[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param array $options
     */
    private function setOptions(array $options)
    {
        $this->setAuthenticator($options);
        $this->setRules($options);
        $this->setOnUnauthorizedCallback($options);
        $this->setOnSuccessCallback($options);
    }

    /**
     * @param array $options
     */
    private function setAuthenticator(array $options)
    {
        if (!array_key_exists('authenticator', $options)) {
            throw new \RuntimeException('Option "authenticator" is required');
        }

        if (!$options['authenticator'] instanceof AuthenticatorInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Option "authenticator" should be instance of Slim\Authenticator\AuthenticatorInterface, %s given',
                get_class($options['authenticator'])
            ));
        }

        $this->authenticator = $options['authenticator'];
    }

    /**
     * @param array $options
     */
    private function setRules(array $options)
    {
        if (!array_key_exists('rules', $options)) {
            $this->rules = [
                new PathRule(),
                new MethodRule()
            ];
        } else {
            foreach ($options['rules'] as $rule) {
                $this->addRule($rule);
            }
        }
    }

    /**
     * @param RuleInterface $rule
     */
    private function addRule($rule)
    {
        if (!$rule instanceof RuleInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Each option in "rules" array should be instance of Slim\Rule\RuleInterface, %s given',
                get_class($rule)
            ));
        }

        $this->rules[] = $rule;
    }

    /**
     * @param array $options
     */
    private function setOnUnauthorizedCallback(array $options)
    {
        if (!array_key_exists('onUnauthorized', $options)) {
            return;
        }

        if (!is_callable($options['onUnauthorized'])) {
            throw new \InvalidArgumentException(sprintf(
                'Option "onUnauthorized" should be callable, %s given',
                gettype($options['onUnauthorized'])
            ));
        }

        $this->onUnauthorizedCallback = $options['onUnauthorized'];
    }

    /**
     * @param array $options
     */
    private function setOnSuccessCallback(array $options)
    {
        if (!array_key_exists('onSuccess', $options)) {
            return;
        }

        if (!is_callable($options['onSuccess'])) {
            throw new \InvalidArgumentException(sprintf(
                'Option "onSuccess" should be callable, %s given',
                gettype($options['onSuccess'])
            ));
        }

        $this->onSuccessCallback = $options['onSuccess'];
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    private function isAuthenticable(RequestInterface $request)
    {
        foreach ($this->rules as $rule) {
            if ($rule->handle($request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param RequestInterface      $request
     * @param ResponseInterface     $response
     * @param UnauthorizedException $e
     * @return ResponseInterface
     */
    private function onUnauthorized(RequestInterface $request, ResponseInterface $response, UnauthorizedException $e)
    {
        $response = $this->authenticator->onUnauthorized($request, $response, $e);
        if (null === $this->onUnauthorizedCallback) {
            return $response;
        }

        $unauthorizedCallbackResult = call_user_func_array($this->onUnauthorizedCallback, [$request, $response, $e]);
        if ($unauthorizedCallbackResult instanceof ResponseInterface) {
            return $unauthorizedCallbackResult;
        }

        return $response;
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param mixed             $data
     */
    private function onSuccess(RequestInterface $request, ResponseInterface $response, $data)
    {
        if (null !== $this->onSuccessCallback) {
            call_user_func_array($this->onSuccessCallback, [$request, $response, $data]);
        }
    }
}
