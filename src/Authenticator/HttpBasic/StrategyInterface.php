<?php

namespace Slim\Authenticator\HttpBasic;

interface StrategyInterface
{
    /**
     * @param string $user
     * @param string $password
     */
    public function authenticate($user, $password);
}
