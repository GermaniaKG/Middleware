<?php

namespace Germania\Middleware;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class LogExceptionMiddleware
{
    /**
     * @var LoggerInterface
     */
    public $log;


    /**
     * @param LoggerInterface $log
     * @param float           $start_time Script start time as float, defaults to "now"
     */
    public function __construct(LoggerInterface $log)
    {
        $this->log = $log;
    }


    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        try
        {
            // Try to do business as usual...
            return $next($request, $response);
        }

        // Executed only in PHP 7, will not match in PHP 5.x
        catch (\Throwable $e)
        {
            $this->handleThrowable( $e );
            throw $e;
        }

        // Executed only in PHP 5.x, will not be reached in PHP 7
        catch (\Exception $e)
        {
            $this->handleThrowable( $e );
            throw $e;
        }

        // Anything else NOT caught here will bubble up.
    }


    /**
     * @param  \Exception|\Throwable $e
     */
    public function handleThrowable( $e )
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

        $this->log->warning($e->getMessage(), $context);
    }

}
