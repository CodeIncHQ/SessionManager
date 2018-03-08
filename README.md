# Session manager service
This library is a simple session management service written in PHP 7. The library is compatible with PSR-15 and PSR-7. It provides a PSR-15 middleware to attach the session manager to the PSR-7 request object as an attribute. 

Saving and writting the session goes through a session handler implementing the native PHP interface [`SessionHandlerInterface`](http://php.net/manual/en/class.sessionhandlerinterface.php).

## Usage 
```php
<?php
use CodeInc\Session\Manager\SessionManager;

// the session manager need the request object and a session handler to start
$sessionManager = new SessionManager(
	$psr7ServerRequest, // the PSR-7 server request 
	new MySessionHandler //  any handler implementing \SessionHandlerInterface
);
$sessionManager->setName("AGreatSession");
$sessionManager->setExpire(30); // minutes
$sessionManager->setValidateClientIp(true);
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
use CodeInc\Session\Middleware\SessionMiddlewareInstantiator;

// a PST-15 middleware is provided to attach to session manager to the request object
// and to send out the session cookie by attaching them to the PSR-7 response.
$middleware = new SessionMiddleware(
	// receives the instantiator which can be custom designed
	// (by implementing `SessionMiddlewareInstantiatorInterface`)
	// if the session manager requires a specific configuration you
	// can also use the default instantiator which just
	// needs the session handler (implementing \SessionHandlerInterface)
	new SessionMiddlewareInstantiator(new MySessionHandler) 
);
$psr7Response = $middleware->process(
	$psr7ServerRequest, 
	$somePsr15RequestHandler
);
```

## Installation
This library is available through [Packagist](https://packagist.org/packages/codeinchq/lib-session) and can be installed using [Composer](https://getcomposer.org/): 

```bash
composer require codeinchq/lib-session
```

## License
This library is published under the MIT license (see the [LICENSE](https://github.com/CodeIncHQ/lib-session/blob/master/LICENSE) file). 

