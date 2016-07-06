<?php

namespace Germania\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ScriptRuntimeMiddleware
{
    /**
     * @var LoggerInterface
     */
    public $log;

    /**
     * @var float
     */
    public $start_time;

    /**
     * @param LoggerInterface $log
     * @param float           $start_time Script start time as float, defaults to "now"
     */
    public function __construct(LoggerInterface $log, $start_time = null)
    {
        $this->log = $log;
        $this->start_time = $start_time ?: microtime('float');
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

        // call next Middleware
        $response = $next($request, $response);

        $this->log->info('Script runtime: ', [
            'seconds' => (microtime('float') - $this->start_time),
        ]);

        return $response;
    }
}
