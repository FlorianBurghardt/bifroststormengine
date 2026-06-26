<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Response;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class ResponseTest extends TestKernel
{
	#region public tests
	public function testConstructorStoresValuesCorrectly(): void
	{
		$status  = HTTPStatusCode::OK;
		$headers = [
			'X-Test' => ['123'],
		];
		$body    = 'Hello World';
		$version = '2.0';

		$response = new Response(
			statusCode: $status,
			headers: $headers,
			body: $body,
			protocolVersion: $version
		);

		$this->assertEquals($status, $response->getStatusCode(), 'StatusCode does not match.');
		$this->assertEquals($headers, $response->getHeaders(), 'Headers do not match.');
		$this->assertEquals($body, $response->getBody(), 'Body does not match.');
		$this->assertEquals($version, $response->getProtocolVersion(), 'ProtocolVersion does not match.');
	}

	public function testWithHeaderReturnsNewInstanceAndDoesNotMutateOriginal(): void
	{
		$response = new Response(HTTPStatusCode::OK);
		$response2 = $response->withHeader('X-Test', 'abc');

		$this->assertEquals([], $response->getHeaders(), 'Original-Response should not have any headers.');
		$this->assertEquals('abc', $response2->getHeaderLine('X-Test'));
		$this->assertFalse($response === $response2, 'withHeader() must deliver a new instance.');
	}

	public function testWithBodyReturnsNewInstance(): void
	{
		$response = new Response(HTTPStatusCode::OK);
		$response2 = $response->withBody('data');

		$this->assertEquals('', $response->getBody(), 'Original-Body should not been changed.');
		$this->assertEquals('data', $response2->getBody(), 'New Body is not set.');
		$this->assertFalse($response === $response2, 'withBody() must deliver a new instance.');
	}

	public function testWithStatusReturnsNewInstance(): void
	{
		$response  = new Response(HTTPStatusCode::OK);
		$response2 = $response->withStatus(HTTPStatusCode::NOT_FOUND);

		$this->assertEquals(HTTPStatusCode::OK, $response->getStatusCode(), 'Original-Status should not been changed.');
		$this->assertEquals(HTTPStatusCode::NOT_FOUND, $response2->getStatusCode(), 'New Status is not set.');
		$this->assertFalse($response === $response2, 'withStatus() must deliver a new instance.');
	}

	public function testGetHeaderLineIsCaseInsensitiveAndCombinesValues(): void
	{
		$response = new Response(
			statusCode: HTTPStatusCode::OK,
			headers: [
				'X-Test' => ['a', 'b'],
			]
		);

		$this->assertEquals('a, b', $response->getHeaderLine('x-test'));
		$this->assertEquals('a, b', $response->getHeaderLine('X-TEST'));
	}

	public function testGetHeaderLineReturnsNullWhenHeaderNotPresent(): void
	{
		$response = new Response(HTTPStatusCode::OK);

		$this->assertEquals(null, $response->getHeaderLine('Missing'));
	}
	#endregion
}