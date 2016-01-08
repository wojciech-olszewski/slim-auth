<?php

namespace Slim\Rule;

use Psr\Http\Message\RequestInterface;

interface RuleInterface
{
    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function handle(RequestInterface $request);
}
