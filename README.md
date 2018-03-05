# Session manager service
This service is a simple session management library written in PHP 7. The library is compatible with PSR-15 and PSR-7. It provides a PSR-15 middleware to attach the session manager to the PSR-7 request object as an attribute. 

Saving and writting the session goes through a session handler implementing the `SessionHandlerInterface`.

## Usage 
```php
<?php
use CodeInc\Session\SessionManager;
use CodeInc\Session\Middleware\SessionMiddleware;
use GuzzleHttp\Psr7\ServerRequest;

$psr7ServerRequest = ServerRequest::fromGlobals();

$sessionManager = new SessionManager($psr7ServerRequest, new MySesionHandler());
$sessionManager->setName("AGreatSession");

$middleware = new SessionMiddleware($sessionManager);
$psr7Response = $middleware->process($psr7ServerRequest, $psr15RequestHandler);
```

## Installation

**To be written**

## License

This library is published under the MIT license (see the [LICENSE](https://github.com/CodeIncHQ/lib-session/blob/master/LICENSE) file). 

