<?php

namespace Slim;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Authenticator\AuthenticatorInterface;
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
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $response;
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
    }

    /**
     * @param array $options
     */
    private function setAuthenticator(array $options)
    {
        if (!array_key_exists('authenticator', $options)) {
            throw new \RuntimeException('Option "authenticator" is required');
        }

        if (!is_a($options['authenticator'], 'Slim\Authenticator\AuthenticatorInterface')) {
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
        if (!is_a($rule, 'Slim\Rule\RuleInterface')) {
            throw new \InvalidArgumentException(sprintf(
                'Option "authenticator" should be instance of Slim\Rule\RuleInterface, %s given',
                get_class($rule)
            ));
        }

        $this->rules[] = $rule;
    }
}
