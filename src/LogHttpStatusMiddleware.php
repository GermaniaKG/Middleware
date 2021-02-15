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

/**
 * Writes the HTTP Response's status code and reason to a PSR-3 Logger.
 */
class LogHttpStatusMiddleware implements MiddlewareInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;


    /**
     * @param LoggerInterface $logger    PSR-3 Logger instance
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
        $response = $handler->handle($request);

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



    /**
     * PSR-7 Double Pass
     *
     * @param RequestInterface   $request   Request instance
     * @param ResponseInterface  $response  Response instance
     * @param callable           $next      Middelware callable
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
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
