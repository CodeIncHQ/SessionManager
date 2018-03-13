# Session manager service
This library is a simple session management service written in PHP 7. The library is compatible with PSR-15 and PSR-7. It provides a PSR-15 middleware to attach the session manager to the PSR-7 request object as an attribute. 

Saving and writting the session goes through a session handler implementing [`HandlerInterface`](src/Handlers/HandlerInterface.php).

## Usage 
```php
<?php
use CodeInc\SessionManager\SessionManager;

// the session manager need a session handler to start
$sessionManager = new SessionManager(
	new MySessionHandler() //  any handler implementing HandlerInterface
);
$sessionManager->setName("AGreatSession");
$sessionManager->setExpire(30); // minutes
$sessionManager->setValidateClientIp(true);
$session = $sessionManager->start($psr7ServerRequest); // the PSR-7 server request

// SessionManager implement ArrayAccess 
$session["test"] = "Hello wold!";
echo $session["test"];

// SessionManager is also iterable
foreach ($session as $var => $value) {
	echo "$var = $value\n";
}
```

### Middleware
A [PSR-15](https://www.php-fig.org/psr/psr-15/) [middleware](https://www.php-fig.org/psr/psr-15/#22-psrhttpservermiddlewareinterface) [`SessionMiddleware`](src/SessionMiddleware.php) is provided to attach to session manager to the request object and to send out the session cookie by attaching them to the PSR-7 response. The cookie is only attached to `text/html` responses. 

```php
<?php
use CodeInc\SessionManager\SessionManager;
use CodeInc\SessionManager\SessionMiddleware;

// the middleware needs the session manager
$sessionManager = new SessionManager(new MySessionHandler());

// instantiating the middleware and processing the PSR-7 request, producing a PSR-7 response
// the middleware will take car of starting the session and will attache the session
// data to the PSR-7 request attributes.
$middleware = new SessionMiddleware($sessionManager);
$psr7Response = $middleware->process(
	$psr7ServerRequest, 
	$somePsr15RequestHandler
);
```
Withing a controller or another middleware you and access the session data from the PSR-7 request attributes using:
```php
<?php
use CodeInc\SessionManager\SessionMiddleware;

$session = SessionMiddleware::getSession($psr7ServerRequest);
$session["user_name"] = "John Smith";
echo $session["user_name"];
```

## Installation
This library is available through [Packagist](https://packagist.org/packages/codeinc/sessionmanager) and can be installed using [Composer](https://getcomposer.org/): 

```bash
composer require codeinc/sessionmanager
```

## License
This library is published under the MIT license (see the [LICENSE](LICENSE) file). 

