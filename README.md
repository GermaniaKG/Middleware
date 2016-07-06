#Germania Middleware

**Middleware for Slim3 based web apps**


##EmailExceptionMiddleware

```php
<?php
use Germania\Middleware\EmailExceptionMiddleware;

$app = new Slim\App;

$mailer_factory = function() {
	return Swift_Mailer::newInstance( ... );
};

$message_factory = function() {
	return Swift_Message::newInstance();
};

$middleware = new EmailExceptionMiddleware("My APP", $mailer_factory, $message_factory);
$app->add( $middleware );
```

##ScriptRuntimeMiddleware

Logs the time taken from instantiation to the time when the _next_ middlewares have been executed. It uses the **info()** method described in [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) [LoggerInterface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#3-psrlogloggerinterface) 


```php
<?php
use Germania\Middleware\ScriptRuntimeMiddleware;

$app = new Slim\App;
$logger = new \Monolog\Logger;

$app->add( new ScriptRuntimeMiddleware($logger) );
```


##LogExceptionMiddleware


Logs information about exceptions thrown during _next_ middlewares execution. It uses the **warning()** method described in [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) [LoggerInterface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#3-psrlogloggerinterface) 

```php
<?php
use Germania\Middleware\LogExceptionMiddleware;

$app = new Slim\App;
$logger = new \Monolog\Logger;

$app->add( new LogExceptionMiddleware($logger) );
```



##Development

Clone that repo, dive into directory and install Composer dependencies.

```bash
# Clone and install
$ git clone git@bitbucket.org:germania/middleware <directory>
$ cd <directory>
$ composer install
```
