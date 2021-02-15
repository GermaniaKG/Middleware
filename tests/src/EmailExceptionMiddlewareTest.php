<?php
namespace tests;

use Germania\Middleware\EmailExceptionMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EmailExceptionMiddlewareTest extends \PHPUnit\Framework\TestCase
{
    use ProphecyTrait;

    public function testInstantiationAndInterfaces() : EmailExceptionMiddleware
    {
        $mailer_factory = $this->createSwiftMailerFactory();
        $message_factory = $this->createSwiftMessageFactory();

        $sut = new EmailExceptionMiddleware("TestApp", $mailer_factory, $message_factory);

        $this->assertInstanceOf( MiddlewareInterface::class, $sut );

        return $sut;
    }


    /**
     * @depends testInstantiationAndInterfaces
     */
    public function testSinglePassMiddlewareInterface( EmailExceptionMiddleware $sut ) : void
    {
        // Setup dependencies
        $request = $this->prophesize( ServerRequestInterface::class );
        $request_mock = $request->reveal();

        $handler = $this->prophesize( RequestHandlerInterface::class );
        $handler->handle( Argument::exact($request_mock) )->shouldBeCalled();
        $handler_mock = $handler->reveal();

        // Setup SUT
        $result = $sut->process( $request_mock, $handler_mock);
        $this->assertInstanceOf( ResponseInterface::class, $result );

    }


    /**
     * @depends testInstantiationAndInterfaces
     */
    public function testDoublePassMiddlewareInterface(EmailExceptionMiddleware $sut ) : void
    {
        // Setup dependencies
        $exception_message = "Yay!";
        $next = function( $request, $response ) use ($exception_message) { throw new \Exception($exception_message, 1, new \Exception); };

        $request = $this->prophesize(RequestInterface::class);
        $request_mock = $request->reveal();

        $response = $this->prophesize(ResponseInterface::class);
        $response_mock = $response->reveal();

        // Setup SUT
        $this->expectException( \Exception::class );
        $sut( $request_mock, $response_mock, $next);
    }




    public function createSwiftMailerFactory() : callable
    {
        $mailer = $this->prophesize( \Swift_Mailer::class );
        $mailer_mock = $mailer->reveal();

        return function() use ($mailer_mock) {
            return $mailer_mock;
        };
    }


    public function createSwiftMessageFactory() : callable
    {

        $message = $this->prophesize( \Swift_Message::class );
        $message_mock = $message->reveal();

        return function() use ($message_mock) {
            return $message_mock;
        };
    }


}
