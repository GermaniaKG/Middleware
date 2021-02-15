<?php
namespace tests;

use Germania\Middleware\ScriptRuntimeMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;


class ScriptRuntimeMiddlewareTest extends \PHPUnit\Framework\TestCase
{
    use ProphecyTrait;

	public function testInstantiationAndInterfaces() : ScriptRuntimeMiddleware
	{
		// Setup dependencies
		$logger = $this->prophesize(LoggerInterface::class);
		$logger_mock = $logger->reveal();

		// Setup SUT
		$sut = new ScriptRuntimeMiddleware( $logger_mock );
		$this->assertInstanceOf( MiddlewareInterface::class, $sut );
        $this->assertInstanceOf( LoggerAwareInterface::class, $sut );

        return $sut;
	}


    /**
     * @depends testInstantiationAndInterfaces
     */
	public function testDoublePass( ScriptRuntimeMiddleware $sut )
	{
		// Setup SUT
		$logger = $this->prophesize(LoggerInterface::class);
		$logger->info( Argument::type("string"), Argument::type("array"))->shouldBeCalled();
		$logger_mock = $logger->reveal();

		$sut->setLogger($logger_mock );


		// Prepare PSR-7 stuff
		$request = $this->prophesize(RequestInterface::class);
		$request_mock = $request->reveal();

		$response = $this->prophesize(ResponseInterface::class);
		$response_mock = $response->reveal();

		$next = function( $request, $response ) { return $response; };

		// Perform test

		$result_response = $sut( $request_mock, $response_mock, $next);
		$this->assertEquals( $result_response, $response_mock);
		$this->assertInstanceOf( ResponseInterface::class, $result_response);

	}



    /**
     * @depends testInstantiationAndInterfaces
     */
	public function testSinglePass( ScriptRuntimeMiddleware $sut )
	{
		// Setup SUT
		$logger = $this->prophesize(LoggerInterface::class);
		$logger->info( Argument::type("string"), Argument::type("array"))->shouldBeCalled();
		$logger_mock = $logger->reveal();
		$sut->setLogger($logger_mock );


		// Prepare PSR-7 stuff
		$response = $this->prophesize(ResponseInterface::class);
		$response_mock = $response->reveal();

		$request = $this->prophesize( ServerRequestInterface::class );
		$request_mock = $request->reveal();

		$handler = $this->prophesize( RequestHandlerInterface::class );
		$handler->handle( Argument::exact($request_mock) )->willReturn( $response_mock );
		$handler_mock = $handler->reveal();

		// Perform test
		$result_response = $sut->process( $request_mock, $handler_mock);
		$this->assertEquals( $result_response, $response_mock);
		$this->assertInstanceOf( ResponseInterface::class, $result_response);
	}


}
