<?php
namespace Germania\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;





/**
 * Writes the HTTP Response's status code and reason to a PSR-3 Logger.
 */
class LogHttpStatusMiddleware
{
    use LoggerAwareTrait;


    /**
     * @param LoggerInterface $logger    PSR-3 Logger instance
     */
    public function __construct( LoggerInterface $logger )
    {
        $this->logger = $logger;
    }



    /**
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @param  callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke( ServerRequestInterface $request, ResponseInterface $response, callable $next )
    {
        // Do nothing before $next,
        // instead call $next first.
        $response = $next($request, $response);


        // Now that all is done,
        // concat to a string and send it to the PSR-3 Logger.
        $status = $response->getStatusCode();
        $reason = $response->getReasonPhrase();

        $msg = sprintf("%s %s", $status, $reason);

        $this->logger->info("Response", [
            'status' => $msg
        ]);

        return $response;
    }

}
