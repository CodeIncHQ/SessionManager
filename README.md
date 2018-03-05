# Session manager service
This library is a simple session management service written in PHP 7. The library is compatible with PSR-15 and PSR-7. It provides a PSR-15 middleware to attach the session manager to the PSR-7 request object as an attribute. 

Saving and writting the session goes through a session handler implementing the `SessionHandlerInterface`.

## Usage 
```php
<?php
use CodeInc\Session\SessionManager;
use GuzzleHttp\Psr7\ServerRequest;
use CodeInc\Session\SessionHandlerInterface;

// handling the session data goes through a handler class
final class MySesionHandler implements SessionHandlerInterface {
	// reads session data
    public function readData(string $sessionId):array {};

   // write session data
    public function writeData(string $sessionId, array $data):void {};

    // removes session data
    public function remove(string $sessionId):void {};
}

// the session manager need the request object and a session handler to start
$psr7ServerRequest = ServerRequest::fromGlobals();
$sessionManager = new SessionManager($psr7ServerRequest, new MySesionHandler());
$sessionManager->setName("AGreatSession");
$sessionManager->setExpire(30); // minutes
$sessionManager->validateClientIp(true);
$sessionManager->start();

// SessionManager implement ArrayAccess 
$sessionManager["test"] = "Hello wold!";
echo $sessionManager["test"];

// SessionManager is also iterable
foreach ($sessionManager as $var => $value) {
	echo "$var = $value\n";
}
```

### Middleware 
```php
<?php
use CodeInc\Session\Middleware\SessionMiddleware;

// a PST-15 middleware is provided to attach to session manager to the request object
// and to send out the session cookie by attaching them to the PSR-7 response.
$middleware = new SessionMiddleware($sessionManager);
$psr7Response = $middleware->process($psr7ServerRequest, $somePsr15RequestHandler);
```

A trait `SessionControllerTrait` is provided to allow access to the session manager from within a controller when the request has been processed using `SessionMiddleware`.


## Installation
This library is available through [Packagist](https://packagist.org/packages/codeinchq/lib-session) and can be installed using [Composer](https://getcomposer.org/): 

```bash
composer require codeinchq/lib-session
```

## License
This library is published under the MIT license (see the [LICENSE](https://github.com/CodeIncHQ/lib-session/blob/master/LICENSE) file). 

