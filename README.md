<img src="https://static.germania-kg.com/logos/ga-logo-2016-web.svgz" width="250px">

------




# Germania KG · Middleware

**Collection of useful PSR-15 Single Pass and Double Pass middleware we use in our apps**

[![Packagist](https://img.shields.io/packagist/v/germania-kg/middleware.svg?style=flat)](https://packagist.org/packages/germania-kg/middleware)
[![PHP version](https://img.shields.io/packagist/php-v/germania-kg/middleware.svg)](https://packagist.org/packages/germania-kg/middleware)
[![Build Status](https://img.shields.io/travis/GermaniaKG/Middleware.svg?label=Travis%20CI)](https://travis-ci.org/GermaniaKG/Middleware)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/GermaniaKG/Middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/GermaniaKG/Middleware/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/GermaniaKG/Middleware/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/GermaniaKG/Middleware/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/GermaniaKG/Middleware/badges/build.png?b=master)](https://scrutinizer-ci.com/g/GermaniaKG/Middleware/build-status/master)



## Installation with Composer

```bash
$ composer require germania-kg/middleware
```



## LogHttpStatusMiddleware

Writes the HTTP Response's status code and reason to a PSR-3 Logger after *$next* has finished, using *Psr\Log\LoggerInterface::info* method. While this middleware is PSR-15 compliant, here a Slim3 example:

```php
<?php
use Germania\Middleware\LogHttpStatusMiddleware;

$app        = new Slim\App;
$logger     = new \Monolog\Logger;
$middleware = new LogHttpStatusMiddleware( $logger);

$app->add( $middleware );
```



## EmailExceptionMiddleware

While this middleware is PSR-15 compliant, here a Slim3 example:

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

#### Bonus: Display exception information 

```php
<?php
use Germania\Middleware\EmailExceptionMiddleware;

$middleware = new EmailExceptionMiddleware("My APP", $mailer_factory, $message_factory);

try {
	throw new \Exception("Huh?");
}
catch (\Exception $e) {
	echo $middleware->render( $e );
}
```






## ScriptRuntimeMiddleware

Logs the time taken from instantiation to the time when the _next_ middlewares have been executed. It uses the **info()** method described in [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) [LoggerInterface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#3-psrlogloggerinterface) . While this middleware is PSR-15 compliant, here a Slim3 example:


```php
<?php
use Germania\Middleware\ScriptRuntimeMiddleware;

$app = new Slim\App;
$logger = new \Monolog\Logger;

$app->add( new ScriptRuntimeMiddleware($logger) );
```



## LogExceptionMiddleware


Logs information about exceptions thrown during _next_ middlewares execution. It uses the **warning()** method described in [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) [LoggerInterface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#3-psrlogloggerinterface). While this middleware is PSR-15 compliant, here a Slim3 example:

```php
<?php
use Germania\Middleware\LogExceptionMiddleware;

$app = new Slim\App;
$logger = new \Monolog\Logger;

$app->add( new LogExceptionMiddleware($logger) );
```



## Development

Clone that repo, dive into directory and install Composer dependencies. 

```bash
# Clone and install
$ git clone https://github.com/GermaniaKG/Middleware.git <directory>
$ cd <directory>
$ composer install
```

## Unit tests

Either copy `phpunit.xml.dist` to `phpunit.xml` and adapt to your needs, or leave as is. Run [PhpUnit](https://phpunit.de/) test or composer scripts like this:

```bash
$ composer test
# or
$ vendor/bin/phpunit
```
