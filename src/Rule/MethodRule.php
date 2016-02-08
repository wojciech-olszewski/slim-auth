<?php

namespace Slim\Rule;

use Psr\Http\Message\RequestInterface;

class MethodRule implements RuleInterface
{
    /**
     * @var array
     */
    private $methods = [
        'OPTIONS',
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'DELETE',
    ];

    /**
     * @var array
     */
    private $excluded = [];

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
    public function handle(RequestInterface $request)
    {
        $method = $request->getMethod();

        if ($this->shouldExclude($method)) {
            return false;
        }

        return $this->shouldHandle($method);
    }

    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return array
     */
    public function getExcluded()
    {
        return $this->excluded;
    }

    /**
     * @param array $options
     */
    private function setOptions(array $options = [])
    {
        if (array_key_exists('methods', $options)) {
            $this->methods = $this->normalizeMethods($options['methods']);
        }

        if (array_key_exists('excluded', $options)) {
            $this->excluded = $this->normalizeMethods($options['excluded']);
        }
    }

    /**
     * @param array $methods
     * @return array
     */
    private function normalizeMethods(array $methods = [])
    {
        return array_map(function ($method) {
            return mb_strtoupper($method);
        }, $methods);
    }

    /**
     * @param $method
     * @return bool
     */
    private function shouldExclude($method)
    {
        return in_array($method, $this->getExcluded());
    }

    /**
     * @param $method
     * @return bool
     */
    private function shouldHandle($method)
    {
        return in_array($method, $this->getMethods());
    }
}
