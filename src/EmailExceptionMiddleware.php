<?php

namespace Germania\Middleware;

use Germania\Middleware\Exceptions\FactoryException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class EmailExceptionMiddleware
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
     * @param callable $mailer_factory
     * @param callable $message_factory
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
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            // Try to do business as usual...
            return $next($request, $response);
        } catch (\Exception $e) {
            $text = $this->render($e);

            $message = $this->getMessage();
            $message->setContentType('text/html')
                    ->setSubject('['.$this->app_name.']: Exception Abort: '.get_class($e))
                    ->setBody($text);

            $mailer = $this->getMailer();
            $mailer->send($message);

            throw $e;
        }
    }

    /**
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
        if (!$mailer instanceof Swift_Mailer) {
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
        if (!$message instanceof Swift_Message) {
            throw new FactoryException('Message factory must return Swift_Message instance.');
        }

        return $message;
    }
}
