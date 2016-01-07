<?php

namespace Slim;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class SlimAuth
{
    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param callable          $next
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $response;
    }
}
