<?php
namespace tests;

use Germania\Middleware\LogExceptionMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogExceptionMiddlewareTest extends \PHPUnit\Framework\TestCase
{
    use ProphecyTrait;

	public function testInstantiationAndInterfaces() : LogExceptionMiddleware
	{
		// Setup dependencies
		$logger = $this->prophesize(LoggerInterface::class);
		$logger_mock = $logger->reveal();

		// Setup SUT
		$sut = new LogExceptionMiddleware( $logger_mock );
		$this->assertInstanceOf( MiddlewareInterface::class, $sut );
        $this->assertInstanceOf( LoggerAwareInterface::class, $sut );

        return $sut;

	}


    /**
     * @depends testInstantiationAndInterfaces
     */
	public function testSinglePassMiddlewareInterface( LogExceptionMiddleware $sut) : void
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
	public function testDoublePassMiddlewareInterface( LogExceptionMiddleware $sut) : void
	{

        // Setup dependencies
		$exception_message = "Yay!";
		$next = function( $request, $response ) use ($exception_message) { throw new \Exception($exception_message, 1, new \Exception); };

        // Setup SUT
        $logger = $this->prophesize(LoggerInterface::class);
        $logger->warning( $exception_message, Argument::type("array"))->shouldBeCalled();
        $logger_mock = $logger->reveal();
        $sut->setLogger($logger_mock);


		$request = $this->prophesize(RequestInterface::class);
		$request_mock = $request->reveal();

		$response = $this->prophesize(ResponseInterface::class);
		$response_mock = $response->reveal();

        // Test
		$this->expectException( \Exception::class );
		$result_response = $sut( $request_mock, $response_mock, $next);

	}


}
