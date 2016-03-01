# SlimAuth
SlimAuth is a middleware for [Slim Framework](http://www.slimframework.com/) which allows to handle authentication process using various authenticators. It is easily configurable and adjustable library. Currently implemented authenticators are:
* [Http Basic](https://en.wikipedia.org/wiki/Basic_access_authentication) (HttpBasicAuthenticator)
* [JWT](http://jwt.io/) (JsonWebTokenAuthenticator) 

### Requirements
* Library require [Slim Framework](http://www.slimframework.com/) with version >= 3.0
* Library require PHP 5.5

### Installation by composer
---
@todo

### Usage
---
#### 1. Configuration
```php
<?php

$app = new \Slim\App();

$app->add(new \Slim\SlimAuth([
  // optional array of RuleInterface instances
  'rules' => [ ... ],
  
  // mandatory field which require instance of AuthenticatorInterface,
  'authenticator' => ...,
  
  // optional callback when authentication failed
  // $exception object is throwed by authenticator and contains more details about the failure reason
  'onUnauthorized' => function (
    \Slim\Http\Request $request, 
    \Slim\Http\Response $response, 
    \Slim\Exception\UnauthorizedException $exception
  ) {
    ...
  },
  
  // optional callback when authentication success
  'onSuccess' => function (
    \Slim\Http\Request $request, 
    \Slim\Http\Response $response, 
    $result
  ) {
    ...
  }
]));

$app->run();
```

#### 2. Rules
Rules are objects which determine if certain Request should or should not be authenticated. Interface body is as follow:
```php
<?php

use Psr\Http\Message\RequestInterface;

interface RuleInterface
{
    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function handle(RequestInterface $request);
}
```
Existing Rules are:
##### 2.1. PathRule
```php
<?php

$authMiddleware = new \Slim\SlimAuth([
  'rules' => [
    new \Slim\Rule\PathRule([
      // default value is '/'
      'path' => '/secured/path',
      
      // optional array of excluded paths
      'excluded' => [ ... ]
    ])
  ]
]);
```
##### 2.2. MethodRule
```php
<?php

$authMiddleware = new \Slim\SlimAuth([
  'rules' => [
    new \Slim\Rule\MethodRule([
      // array of secured methods, default value is ['OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'DELETE'];
      'methods' => [ ... ],
      
      // optional array of excluded methods
      'excluded' => [ ... ]
    ])
  ]
]);
```
If many Rules are defined for SlimAuth, then Request will be handled by authenticator only when all rules handle Request. For example below configuration:
```php
<?php

$authMiddleware = new \Slim\SlimAuth([
  'rules' => [
    new \Slim\Rule\PathRule(['path' => '/api', 'excluded' => ['/api/doc']],
    new \Slim\Rule\MethodRule(['methods' => ['POST', 'PUT']])
  ]
]);
```
Will handle all POST & PUT requests to /api address but pass through all requests to /api/doc.

#### 3. Authenticators
Authenticators are objects which keep logic responsible for authentication process. Interface body is as follow:
```php
<?php

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Exception\UnauthorizedException;

interface AuthenticatorInterface
{
    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function authenticate(RequestInterface $request);

    /**
     * @param RequestInterface      $request
     * @param ResponseInterface     $response
     * @param UnauthorizedException $e
     * @return ResponseInterface
     */
    public function onUnauthorized(
      RequestInterface $request, 
      ResponseInterface $response, 
      UnauthorizedException $e
    );
}
```
Currently implemented authenticators are:
##### 3.1. HttpBasicAuthenticator
```php
<?php

$authMiddleware = new \Slim\SlimAuth([
  'authenticator' => new HttpBasicAuthenticator([
    // instance of Slim\Authenticator\HttpBasic\StrategyInterface
    'strategy' => ... 
    
    // optional parameter, in case of failure header WWW-Authenticate woth configured value is attached to the Response        // object, default value is "Restricted area"
    'realm' => 'Protected Area' 
  ])
]);
```
Possible ways how to pass and resolve credentials are:
* using server params: 
```
$user from PHP_AUTH_USER
$password from PHP_AUTH_PW
```
* using request header: 
```
Authorization: Basic <encoded_token>
```
Strategy is object with following interface:
```php
<?php

interface StrategyInterface
{
    /**
     * @param string $user
     * @param string $password
     */
    public function authenticate($user, $password);
}
```
Available strategy is UserArrayStrategy
```php
<?php

$authenticator = new HttpBasicAuthenticator([
  'strategy' => new UserArrayStrategy([
    'user_1' => 'password_1',
    'user_2' => 'password_2'
    ...
  ])
])
```
Simple interface allows you to easily implement your own strategy, and compare provided credentials against any provider you want.

##### 3.2. JsonWebTokenAuthenticator

#### 4. onUnauthorized callback
#### 5. onSuccess callback

@todo

### Testing
---
For testing purposes [phpspec](http://phpspec.readthedocs.org/) framework is utilized. In order to invoke tests run:
```
$ composer test
```
[PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) is responsible for keeping code style consistent:
```
$ composer cs
```
Aggregate command which invokes above ones is (invoked within CI build as well):
```
$ composer ci
```

### Contributing
---
If you want to contribute create your own fork, and create PR to master branch. Please be sure your code pass:
```
$ composer test
```
and 
```
$ composer cs
```
