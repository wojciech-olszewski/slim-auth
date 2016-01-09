<?php

namespace Slim\Authenticator\HttpBasic;

use Slim\Exception\UnauthorizedException;

class UserArrayStrategy implements StrategyInterface
{
    /**
     * @var array
     */
    private $users = [];

    /**
     * @param array $users
     */
    public function __construct($users = [])
    {
        $this->users = $users;
    }

    /**
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @inheritdoc
     */
    public function authenticate($user, $password)
    {
        if (!array_key_exists($user, $this->getUsers())) {
            throw new UnauthorizedException(sprintf(
                'User "%s" does not exists',
                $user
            ));
        }

        if ($password !== $this->getUsers()[$user]) {
            throw new UnauthorizedException(sprintf(
                'Passwords for user "%s" do not match',
                $user
            ));
        }

        return true;
    }
}
