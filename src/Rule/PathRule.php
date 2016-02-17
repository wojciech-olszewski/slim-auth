<?php

namespace Slim\Rule;

use Psr\Http\Message\RequestInterface;

class PathRule implements RuleInterface
{
    /**
     * @var string
     */
    private $path;

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
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getExcluded()
    {
        return $this->excluded;
    }

    /**
     * @inheritdoc
     */
    public function handle(RequestInterface $request)
    {
        $path = $this->normalizePath($request->getUri()->getPath());

        if ($this->shouldExclude($path)) {
            return false;
        }

        return $this->shouldHandle($path);
    }

    /**
     * @param array $options
     */
    private function setOptions(array $options)
    {
        $this->path = $this->normalizePath('/');
        if (array_key_exists('path', $options)) {
            if (!is_string($options['path'])) {
                throw new \RuntimeException(sprintf(
                    'Option "path" should be string, %s given',
                    gettype($options['path'])
                ));
            }

            $this->path = $this->normalizePath($options['path']);
        }

        if (array_key_exists('excluded', $options)) {
            if (!is_string($options['excluded']) && !is_array($options['excluded'])) {
                throw new \RuntimeException(sprintf(
                    'Option "excluded" should be string or array, %s given',
                    gettype($options['excluded'])
                ));
            }

            $this->excluded = array_map(function ($pattern) {
                return $this->normalizePath($pattern);
            }, (array) $options['excluded']);
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function normalizePath($path)
    {
        $path = trim($path);
        $path = trim($path, '/');

        return '/' . $path;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function shouldExclude($path)
    {
        foreach ($this->getExcluded() as $excludedPath) {
            if (false !== stripos($path, $excludedPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $path
     * @return bool
     */
    private function shouldHandle($path)
    {
        return false !== stripos($path, $this->getPath());
    }
}
