<?php

namespace SlimTest\Rule;

use Psr\Http\Message\RequestInterface;
use Slim\Rule\RuleInterface;

class FalsyRule implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function handle(RequestInterface $request)
    {
        return false;
    }
}
