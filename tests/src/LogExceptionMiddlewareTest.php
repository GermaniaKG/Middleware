<?php
namespace tests;

use Germania\Middleware\LogExceptionMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogExceptionMiddlewareTest extends \PHPUnit\Framework\TestCase
{

	public function testInstantiationAndInterfaces()
	{
		// Setup dependencies
		$logger = $this->prophesize(LoggerInterface::class);
		$logger_mock = $logger->reveal();

		// Setup SUT
		$sut = new LogExceptionMiddleware( $logger_mock );
		$this->assertInstanceOf( MiddlewareInterface::class, $sut );

	}


	public function testSinglePassMiddlewareInterface()
	{
		// Setup dependencies
		$logger = $this->prophesize(LoggerInterface::class);
		$logger_mock = $logger->reveal();

		$request = $this->prophesize( ServerRequestInterface::class );
		$request_mock = $request->reveal();

		$handler = $this->prophesize( RequestHandlerInterface::class );
		$handler->handle( Argument::exact($request_mock) )->shouldBeCalled();
		$handler_mock = $handler->reveal();

		// Setup SUT
		$sut = new LogExceptionMiddleware( $logger_mock );
		$result = $sut->process( $request_mock, $handler_mock);

		$this->assertInstanceOf( ResponseInterface::class, $result );

	}


	public function testDoublePassMiddlewareInterface()
	{
		$exception_message = "Yay!";

		$next = function( $request, $response ) use ($exception_message) { throw new \Exception($exception_message, 1, new \Exception); };

		// Setup dependencies
		$logger = $this->prophesize(LoggerInterface::class);
		$logger->warning( $exception_message, Argument::type("array"))->shouldBeCalled();
		$logger_mock = $logger->reveal();

		$request = $this->prophesize(RequestInterface::class);	
		$request_mock = $request->reveal();

		$response = $this->prophesize(ResponseInterface::class);	
		$response_mock = $response->reveal();


		// Setup SUT
		$sut = new LogExceptionMiddleware( $logger_mock );

		$this->expectException( \Exception::class );
		$result_response = $sut( $request_mock, $response_mock, $next);

	}


}