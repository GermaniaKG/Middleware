<?php
namespace Germania\Middleware;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class EmailExceptionMiddleware
{

    /**
     * @var ContainerInterface
     */
    public $container;

    /**
     * @var string
     */
    public $app_name;

    /**
     * @var string
     */
    public $include_file;


    /**
     * @param string $app_name
     * @param ContainerInterface $container
     */
    public function __construct( $app_name, ContainerInterface $container)
    {
        $this->app_name  = $app_name;
        $this->container = $container;

        $include_path = realpath(__DIR__ . '/../includes');
        $this->include_file = $include_path . '/exception.php';
    }


    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param Callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke (ServerRequestInterface $request, ResponseInterface $response, callable $next) {

        try {
            return $next($request, $response);
        }
        catch (\Exception $e) {
            $text = $this->renderException( $e );

            $message = $this->container->get('new_message')
                ->setContentType('text/html')
                ->setSubject( "[" . $this->app_name . "]: Exception Abort: " . get_class($e))
                ->setBody( $text );

            $this->container->get('mailer')->send( $message);

            throw $e;
        }

    }

    /**
     * @param Exception $e
     */
    public function renderException( $e ) {
        return require( $this->include_file );
    }
}
