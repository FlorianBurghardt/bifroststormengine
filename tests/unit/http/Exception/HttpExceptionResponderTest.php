<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Exception;

use de\bifroststormengine\core\config\Config;
use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\core\Enum\PHPExceptionType;
use de\bifroststormengine\core\environment\Environment;
use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\core\Exception\HttpErrorHandler;
use de\bifroststormengine\core\FrameworkManifestProvider;
use de\bifroststormengine\http\Exception\HttpExceptionResponder;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class HttpExceptionResponderTest extends TestKernel
{
	#region public tests
	public function testToHttpResponseWrapsGenericExceptionAsInternalError(): void
	{
		$responder = $this->createResponder();

		$exception = new FrameworkException(
			PHPExceptionType::RUNTIME_ERROR,
			12344,
			'Boom'
		);
		$response  = $responder->toHttpResponse($exception);

		$this->assertInstanceOf(Response::class, $response, 'Responder should return a response object.');
		$this->assertTrue(
			$response->getStatusCode()->isServerError(),
			'Generic exceptions should be mapped as ServerError.'
		);

		$body = \json_decode($response->getBody(), true);

		$this->assertNotNull($body, 'Body should be valid JSON.');
		$this->assertTrue(isset($body['error']), 'JSON should contain an error block.');
		$this->assertEquals('Boom', $body['error']['message']);
	}

	public function testToHttpResponseRespectsFrameworkExceptionStatusAndType(): void
	{
		$responder = $this->createResponder();

		$frameworkException = new FrameworkException(
			type: HTTPExceptionType::BAD_REQUEST,
			innerCode: 12345,
			customMessage: 'Invalid input'
		);

		$response = $responder->toHttpResponse($frameworkException);

		$this->assertEquals(HTTPStatusCode::BAD_REQUEST, $response->getStatusCode());

		$body = \json_decode($response->getBody(), true);

		$this->assertEquals('Invalid input', $body['error']['message']);
		$this->assertEquals(12345, $body['error']['innerCode']);
		$this->assertEquals(HTTPExceptionType::BAD_REQUEST->name, $body['error']['type']);
	}

	public function testDebugOutputOnlyInDev(): void
	{
		$config = new Config(['debug' => true]);

		$responder = new HttpExceptionResponder(
			coreErrorHandler: $this->createErrorHandler(),
			config: $config,
			env: Environment::DEV
		);

		$response = $responder->toHttpResponse(
			new \RuntimeException('test')
		);

		$data = \json_decode($response->getBody(), true);

		$this->assertTrue(isset($data['debug']));
	}

	public function testNoDebugOutputInProd(): void
	{
		$config = new Config(['debug' => true]);

		$responder = new HttpExceptionResponder(
			coreErrorHandler: $this->createErrorHandler(),
			config: $config,
			env: Environment::PROD
		);

		$response = $responder->toHttpResponse(
			new \RuntimeException('test')
		);

		$data = \json_decode($response->getBody(), true);

		$this->assertFalse(isset($data['debug']));
	}
	#endregion

	#region private methods
	private function createResponder(): HttpExceptionResponder
	{
		return new HttpExceptionResponder(
			coreErrorHandler: $this->createErrorHandler(),
			config: new Config([]), // Default: debug off
			env: Environment::TEST
		);
	}

	private function createErrorHandler(): HttpErrorHandler
	{
		$manifestProvider = new FrameworkManifestProvider(null);

		return new HttpErrorHandler(
			$manifestProvider
		);
	}
	#endregion
}