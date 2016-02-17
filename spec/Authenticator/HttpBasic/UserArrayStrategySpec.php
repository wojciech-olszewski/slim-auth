<?php

namespace spec\Slim\Authenticator\HttpBasic;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Slim\Exception\UnauthorizedException;

class UserArrayStrategySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('Slim\Authenticator\HttpBasic\UserArrayStrategy');
        $this->shouldImplement('Slim\Authenticator\HttpBasic\StrategyInterface');
    }

    public function it_has_default_options()
    {
        $this->getUsers()->shouldReturn([]);
    }

    public function it_should_throw_exception_when_user_does_not_exists()
    {
        $this
            ->shouldThrow(new UnauthorizedException('User "user" does not exists'))
            ->duringAuthenticate('user', 'password');
    }

    public function it_should_throw_exception_with_invalid_password()
    {
        $this->beConstructedWith([
            'user' => 'password'
        ]);

        $this
            ->shouldThrow(new UnauthorizedException('Passwords for user "user" do not match'))
            ->duringAuthenticate('user', 'wrong_password');
    }

    public function it_return_true_with_valid_credentials()
    {
        $this->beConstructedWith([
            'user' => 'password'
        ]);

        $this->authenticate('user', 'password')->shouldReturn(true);
    }
}
