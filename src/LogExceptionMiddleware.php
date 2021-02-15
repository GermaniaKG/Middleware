<?php

namespace Germania\Middleware;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class LogExceptionMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;


    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }


    /**
     * PSR-15 Single Pass
     *
     * @param  ServerRequestInterface  $request Server reuest instance
     * @param  RequestHandlerInterface $handler Request handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        try {
            $response = $handler->handle($request);
            return $response;
        }
        // Executed only in PHP 7, will not match in PHP 5.x
        catch (\Throwable $e) {
            $this->handleThrowable($e);
            throw $e;
        }

        // Executed only in PHP 5.x, will not be reached in PHP 7
        catch (\Exception $e) {
            $this->handleThrowable($e);
            throw $e;
        }
    }



    /**
     * PSR-7 Double Pass
     *
     * @param RequestInterface   $request   Request instance
     * @param ResponseInterface  $response  Response instance
     * @param callable           $next      Middelware callable
     *
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            // Try to do business as usual...
            return $next($request, $response);
        }

        // Executed only in PHP 7, will not match in PHP 5.x
        catch (\Throwable $e) {
            $this->handleThrowable($e);
            throw $e;
        }

        // Executed only in PHP 5.x, will not be reached in PHP 7
        catch (\Exception $e) {
            $this->handleThrowable($e);
            throw $e;
        }
    }


    /**
     * @param  \Exception|\Throwable $e
     */
    public function handleThrowable($e)
    {
        $context = [
            'class'   => get_class($e),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ];

        if ($f = $e->getPrevious()) {
            $context['previous'] = implode(' / ', [
                $f->getMessage(),
                'class: ' . get_class($f),
                'code: ' . $f->getCode(),
                'file: ' . $f->getFile(),
                'line: ' . $f->getLine(),
            ]);
        }

        $this->logger->warning($e->getMessage(), $context);
    }
}
