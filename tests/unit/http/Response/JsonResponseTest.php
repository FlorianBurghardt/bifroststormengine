<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Response;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Response\JsonResponse;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class JsonResponseTest extends TestKernel
{
	#region public tests
	public function testJsonResponseEncodesDataAndSetsContentType(): void
	{
		$data = ['foo' => 'bar'];

		$response = new JsonResponse(
			data: $data,
			statusCode: HTTPStatusCode::OK
		);

		$decoded = \json_decode($response->getBody(), true);

		$this->assertEquals($data, $decoded, 'Body was not serialized correctly as JSON.');
		$this->assertEquals(HTTPStatusCode::OK, $response->getStatusCode(), 'Status code is incorrect.');
		$this->assertEquals(
			'application/json; charset=utf-8',
			$response->getHeaderLine('Content-Type'),
			'Content type was not set correctly.'
		);
	}

	public function testJsonResponseKeepsCustomHeadersAndOverwritesContentType(): void
	{
		$data = ['foo' => 'bar'];

		$response = new JsonResponse(
			data: $data,
			statusCode: HTTPStatusCode::OK,
			headers: [
				'X-Test'       => ['1'],
				'Content-Type' => ['text/plain'],
			]
		);

		$this->assertEquals('1', $response->getHeaderLine('X-Test'), 'Custom header X-Test is missing or incorrect.');
		$this->assertEquals(
			'application/json; charset=utf-8',
			$response->getHeaderLine('Content-Type'),
			'Content type was not overwritten correctly.'
		);
	}
	#endregion
}