<?php
namespace tests;

use Germania\Middleware\ScriptRuntimeMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;

class ScriptRuntimeMiddlewareTest extends \PHPUnit\Framework\TestCase
{


	public function testSimple()
	{
		// Setup dependencies
		$logger = $this->prophesize(LoggerInterface::class);
		$logger->info( Argument::type("string"), Argument::type("array"))->shouldBeCalled();
		$logger_mock = $logger->reveal();

		$request = $this->prophesize(RequestInterface::class);	
		$request_mock = $request->reveal();

		$response = $this->prophesize(ResponseInterface::class);	
		$response_mock = $response->reveal();

		$next = function( $request, $response ) { return $response; };


		// Setup SUT
		$sut = new ScriptRuntimeMiddleware( $logger_mock );
		$result_response = $sut( $request_mock, $response_mock, $next);


		// Eval
		$this->assertEquals( $result_response, $response_mock);
		$this->assertInstanceOf( ResponseInterface::class, $result_response);

	}


}