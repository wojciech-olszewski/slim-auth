<?php

namespace SlimTest\Rule;

use Psr\Http\Message\RequestInterface;
use Slim\Rule\RuleInterface;

class TruthyRule implements RuleInterface
{
    /**
     * @inheritdoc
     */
    public function handle(RequestInterface $request)
    {
        return true;
    }
}
