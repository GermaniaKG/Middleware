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
	public function testInstantiationAndInterfaces()
	{
		// Setup SUT
		$sut = $this->createSUT();
		$this->assertInstanceOf( MiddlewareInterface::class, $sut );

	}


	public function testSinglePassMiddlewareInterface()
	{
		// Setup SUT
		$sut = $this->createSUT();

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


	public function testDoublePassMiddlewareInterface()
	{
		// Setup SUT
		$sut = $this->createSUT();


		// Test stuff
		$exception_message = "Yay!";

		$next = function( $request, $response ) use ($exception_message) { throw new \Exception($exception_message, 1, new \Exception); };

		// Setup dependencies
		$logger = $this->prophesize(LoggerInterface::class);
		$logger_mock = $logger->reveal();

		$request = $this->prophesize(RequestInterface::class);
		$request_mock = $request->reveal();

		$response = $this->prophesize(ResponseInterface::class);
		$response_mock = $response->reveal();


		// Setup SUT
		$this->expectException( \Exception::class );
		$result_response = $sut( $request_mock, $response_mock, $next);

	}



	protected function createSUT()
	{
		$mailer = $this->prophesize( \Swift_Mailer::class );
		$mailer_mock = $mailer->reveal();

		$mailer_factory = function() use ($mailer_mock) {
			return $mailer_mock;
		};

		$message = $this->prophesize( \Swift_Message::class );
		$message_mock = $message->reveal();

		$message_factory = function() use ($message_mock) {
			return $message_mock;
		};

		return new EmailExceptionMiddleware("TestApp", $mailer_factory, $message_factory);
	}




}
