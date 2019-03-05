<?php

namespace Germania\Middleware;

use Germania\Middleware\Exceptions\FactoryException;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;


class EmailExceptionMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     */
    public $mailer_factory;


    /**
     * @var callable
     */
    public $message_factory;


    /**
     * @var string
     */
    public $app_name;


    /**
     * @var string
     */
    public $include_file;


    /**
     * @param string   $app_name        Name of application (used in email subject)
     * @param callable $mailer_factory  Factory that returns Swift_Mailer instance
     * @param callable $message_factory Factory that returns Swift_Message instance
     */
    public function __construct($app_name, callable $mailer_factory, callable $message_factory)
    {
        $this->app_name = $app_name;

        $this->mailer_factory = $mailer_factory;
        $this->message_factory = $message_factory;

        $include_path = realpath(__DIR__.'/../includes');
        $this->include_file = $include_path.'/exception.php';
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

        
    }     

    

    /**
     * PSR-7 Double Pass
     * 
     * Wrap $next callable in a try-catch block.
     * When an exception is caught, an email will be sent, and the execption will be re-thrown.
     *
     * @param RequestInterface   $request
     * @param ResponseInterface  $response
     * @param callable           $next
     *
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
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
        // Render email body, prepare some things
        $text    = $this->render($e);
        $format  = 'text/html';
        $subject = sprintf("[%s] Exception %s", $this->app_name, get_class($e));

        // Create email message instance
        $message = $this->getMessage();
        $message->setContentType( $format )
                ->setSubject( $subject )
                ->setBody( $text );

        // Create emailer instance + send
        $mailer = $this->getMailer();
        $mailer->send($message);
    }

    /**
     * Creates the email body.
     *
     * In this class, an include file creates a basic information table.
     * Override this method to use your own method.
     *
     * @param Exception $e
     *
     * @return string Exception explanation
     */
    public function render($e)
    {
        return require $this->include_file;
    }


    /**
     * @return Swift_Mailer
     *
     * @throws FactoryException
     */
    public function getMailer()
    {
        $mailer_factory = $this->mailer_factory;
        $mailer = $mailer_factory();
        if (!$mailer instanceof \Swift_Mailer) {
            throw new FactoryException('Mailer factory must return Swift_Mailer instance.');
        }

        return $mailer;
    }


    /**
     * @return Swift_Message
     *
     * @throws FactoryException
     */
    public function getMessage()
    {
        $message_factory = $this->message_factory;
        $message = $message_factory();
        if (!$message instanceof \Swift_Message) {
            throw new FactoryException('Message factory must return Swift_Message instance.');
        }

        return $message;
    }
}
