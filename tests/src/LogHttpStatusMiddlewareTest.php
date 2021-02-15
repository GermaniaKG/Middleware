<?php
namespace tests;

use Germania\Middleware\LogHttpStatusMiddleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogHttpStatusMiddlewareTest extends \PHPUnit\Framework\TestCase
{

    use ProphecyTrait;


    public function testInstantiationAndInterfaces() : LogHttpStatusMiddleware
    {
        $logger = $this->prophesize(LoggerInterface::class);
        $logger_mock = $logger->reveal();

        $sut = new LogHttpStatusMiddleware($logger_mock);

        $this->assertInstanceOf( MiddlewareInterface::class, $sut );
        $this->assertInstanceOf( LoggerAwareInterface::class, $sut );

        return $sut;
    }


	/**
	 * @depends testInstantiationAndInterfaces
     * @dataProvider provideCodesAndReason
	 */
	public function testDoublePass( $code, $reason, $status_message, LogHttpStatusMiddleware $sut)
	{
		// Setup SUT
		$logger = $this->prophesize(LoggerInterface::class);
		$logger->info( Argument::type("string"), [ 'status' => $status_message ])->shouldBeCalled();
		$logger_mock = $logger->reveal();
        $sut->setLogger( $logger_mock );

		// Prepare PSR-7 stuff
		$request = $this->prophesize(ServerRequestInterface::class);
		$request_mock = $request->reveal();

		$response = $this->prophesize(ResponseInterface::class);
		$response->getStatusCode()->willReturn( $code );
		$response->getReasonPhrase()->willReturn( $reason );
		$response_mock = $response->reveal();

		$next = function( $request, $response ) { return $response; };

		// Perform test
		$result_response = $sut( $request_mock, $response_mock, $next);
		$this->assertEquals( $result_response, $response_mock);
		$this->assertInstanceOf( ResponseInterface::class, $result_response);

	}



	/**
     * @depends testInstantiationAndInterfaces
	 * @dataProvider provideCodesAndReason
	 */
	public function testSinglePass( $code, $reason, $status_message, LogHttpStatusMiddleware $sut)
	{
		// Setup SUT
		$logger = $this->prophesize(LoggerInterface::class);
		$logger->info( Argument::type("string"), [ 'status' => $status_message ])->shouldBeCalled();
		$logger_mock = $logger->reveal();
		$sut->setLogger( $logger_mock );


		// Prepare PSR-7 stuff
		$request = $this->prophesize( ServerRequestInterface::class );
		$request_mock = $request->reveal();

		$response = $this->prophesize(ResponseInterface::class);
		$response->getStatusCode()->willReturn( $code );
		$response->getReasonPhrase()->willReturn( $reason );
		$response_mock = $response->reveal();

		$handler = $this->prophesize( RequestHandlerInterface::class );
		$handler->handle( Argument::exact($request_mock) )->willReturn( $response_mock );
		$handler_mock = $handler->reveal();


		// Perform test
		$result_response = $sut->process( $request_mock, $handler_mock);
		$this->assertEquals( $result_response, $response_mock);
		$this->assertInstanceOf( ResponseInterface::class, $result_response);
	}



	public function provideCodesAndReason()
	{
		return [
			"404 Not Found" => [ 400, "Not found", "400 Not found"],
			"200 OK" => [ 200, "OK", "200 OK"]
		];
	}

}
