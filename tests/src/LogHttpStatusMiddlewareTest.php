<?php
namespace tests;

use Germania\Middleware\LogHttpStatusMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;

class LogHttpStatusMiddlewareTest extends \PHPUnit\Framework\TestCase
{


	/**
	 * @dataProvider provideCodesAndReason
	 */
	public function testSimple( $code, $reason, $status_message)
	{
		// Setup dependencies
		$logger = $this->prophesize(LoggerInterface::class);
		$logger->info( Argument::type("string"), [ 'status' => $status_message ])->shouldBeCalled();
		$logger_mock = $logger->reveal();

		$request = $this->prophesize(ServerRequestInterface::class);	
		$request_mock = $request->reveal();

		$response = $this->prophesize(ResponseInterface::class);	
		$response->getStatusCode()->willReturn( $code );	
		$response->getReasonPhrase()->willReturn( $reason );	
		$response_mock = $response->reveal();

		$next = function( $request, $response ) { return $response; };


		// Setup SUT
		$sut = new LogHttpStatusMiddleware( $logger_mock );
		$result_response = $sut( $request_mock, $response_mock, $next);


		// Eval
		$this->assertEquals( $result_response, $response_mock);
		$this->assertInstanceOf( ResponseInterface::class, $result_response);

	}

	public function provideCodesAndReason()
	{
		return [
			[ 400, "Not found", "400 Not found"],
			[ 200, "OK", "200 OK"]
		];
	}

}