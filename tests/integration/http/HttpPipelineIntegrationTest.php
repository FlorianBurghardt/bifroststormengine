<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\integration\http;

use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\core\Exception\HttpErrorHandler;
use de\bifroststormengine\core\FrameworkManifestProvider;
use de\bifroststormengine\http\Exception\HttpExceptionResponder;
use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\http\HttpDispatcher;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\JsonResponse;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Routing\Route;
use de\bifroststormengine\http\Routing\SimpleRouter;
use de\bifroststormengine\tests\TestKernel;
use de\bifroststormengine\http\Enum\HttpMethod;
#endregion

final class HttpPipelineIntegrationTest extends TestKernel
{
	#region public tests
	public function testFullPipelineSuccess(): void
	{
		$dispatcher = $this->createDispatcherWithRoutes();

		$request = new Request(
			method: HttpMethod::GET,
			uri: '/users/10'
		);

		$response = $dispatcher->dispatch($request);

		$this->assertEquals(HTTPStatusCode::OK, $response->getStatusCode());

		$data = \json_decode($response->getBody(), true);
		$this->assertEquals(['id' => '10'], $data);

		$this->assertEquals(
			'application/json; charset=utf-8',
			$response->getHeaderLine('Content-Type')
		);
	}

	public function testFullPipelineErrorHandling(): void
	{
		$dispatcher = $this->createDispatcherWithRoutes();

		$request = new Request(
			method: HttpMethod::GET,
			uri: '/fail'
		);

		$response = $dispatcher->dispatch($request);

		$this->assertEquals(HTTPStatusCode::BAD_REQUEST, $response->getStatusCode());

		$body = \json_decode($response->getBody(), true);

		$this->assertNotNull($body['error'], 'Error block missing');
		$this->assertEquals('Failure triggered', $body['error']['message']);
		$this->assertEquals(44444, $body['error']['innerCode']);
		$this->assertEquals(HTTPExceptionType::BAD_REQUEST->name, $body['error']['type']);

		$this->assertNotNull($body['framework']);
		$this->assertTrue(isset($body['framework']['name']));
		$this->assertTrue(isset($body['framework']['version']));
	}
	#endregion

	#region private methods
	private function createDispatcherWithRoutes(): HttpDispatcher
	{
		$router = new SimpleRouter();

		$router->addRoute(
			new Route(
				methods: [HttpMethod::GET],
				pathPattern: '/users/{id}',
				handler: new class implements HttpHandlerInterface {
					public function handle(Request $request): Response
					{
						$match = $request->getAttribute(HttpDispatcher::ATTR_ROUTE_MATCH);
						$id = $match->getPathParam('id');

						return new JsonResponse(['id' => $id], HTTPStatusCode::OK);
					}
				}
			)
		);

		$router->addRoute(
			new Route(
				methods: [HttpMethod::GET],
				pathPattern: '/fail',
				handler: new class implements HttpHandlerInterface {
					public function handle(Request $request): Response
					{
						throw new FrameworkException(
							type: HTTPExceptionType::BAD_REQUEST,
							innerCode: 44444,
							customMessage: 'Failure triggered'
						);
					}
				}
			)
		);

		$manifestProvider = new FrameworkManifestProvider(null);
		$coreErrorHandler = new HttpErrorHandler($manifestProvider);
		$exceptionResponder = new HttpExceptionResponder($coreErrorHandler);

		return new HttpDispatcher(
			router: $router,
			exceptionResponder: $exceptionResponder,
			middleware: []
		);
	}
	#endregion
}