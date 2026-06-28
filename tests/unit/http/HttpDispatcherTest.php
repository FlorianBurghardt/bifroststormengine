<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http;

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
use de\bifroststormengine\http\Routing\RouteMatch;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\tests\TestKernel;
use de\bifroststormengine\http\Enum\HttpMethod;
#endregion

final class HttpDispatcherTest extends TestKernel
{
	#region public tests
	public function testDispatcherExecutesHandlerAndReturnsResponse(): void
	{
		$handler = new class implements HttpHandlerInterface
		{
			public bool $called = false;

			public function handle(Request $request): Response
			{
				$this->called = true;
				return new JsonResponse(['ok' => true], HTTPStatusCode::OK);
			}
		};

		$router = new class($handler) implements RouterInterface
		{
			public function __construct(private HttpHandlerInterface $handler) {}

			public function addRoute(Route $route): void
			{
				// not used
			}

			public function match(Request $request): RouteMatch
			{
				$route = new Route(
					methods: [HttpMethod::GET],
					pathPattern: '/test',
					handler: $this->handler
				);

				return new RouteMatch($route, []);
			}
		};

		$dispatcher = new HttpDispatcher(
			router: $router,
			exceptionResponder: $this->createExceptionResponder(),
			middleware: []
		);

		$request = new Request(
			method: HttpMethod::GET,
			uri: '/test'
		);

		$response = $dispatcher->dispatch($request);

		$this->assertEquals(HTTPStatusCode::OK, $response->getStatusCode());

		$body = \json_decode($response->getBody(), true);
		$this->assertEquals(['ok' => true], $body);
	}

	public function testDispatcherUsesExceptionResponderOnException(): void
	{
		$handler = new class implements HttpHandlerInterface
		{
			public function handle(Request $request): Response
			{
				throw new FrameworkException(
					type: HTTPExceptionType::BAD_REQUEST,
					innerCode: 22222,
					customMessage: 'Handler failed'
				);
			}
		};

		$router = new class($handler) implements RouterInterface
		{
			public function __construct(private HttpHandlerInterface $handler) {}

			public function addRoute(Route $route): void
			{
				// not used
			}

			public function match(Request $request): RouteMatch
			{
				$route = new Route(
					methods: [HttpMethod::GET],
					pathPattern: '/test',
					handler: $this->handler
				);
				return new RouteMatch($route, []);
			}
		};

		$dispatcher = new HttpDispatcher(
			router: $router,
			exceptionResponder: $this->createExceptionResponder(),
			middleware: []
		);

		$request = new Request(
			method: HttpMethod::GET,
			uri: '/test'
		);

		$response = $dispatcher->dispatch($request);

		$this->assertEquals(HTTPStatusCode::BAD_REQUEST, $response->getStatusCode());

		$data = \json_decode($response->getBody(), true);

		$this->assertEquals('Handler failed', $data['error']['message']);
		$this->assertEquals(22222, $data['error']['innerCode']);
	}

	public function testDispatcherRejectsInvalidMiddleware(): void
	{
		$router = new class implements RouterInterface
		{
			public function addRoute(Route $route): void {}

			public function match(Request $request): RouteMatch
			{
				throw new \RuntimeException('Should not be called');
			}
		};

		$this->assertThrows(
			fn() => new HttpDispatcher(
				router: $router,
				exceptionResponder: $this->createExceptionResponder(),
				middleware: [new \stdClass()] // ❌ invalid
			),
			\InvalidArgumentException::class
		);
	}
	#endregion

	#region private methods
	private function createExceptionResponder(): HttpExceptionResponder
	{
		$manifestProvider = new FrameworkManifestProvider(null);
		$coreHandler      = new HttpErrorHandler($manifestProvider);
		return new HttpExceptionResponder($coreHandler);
	}
	#endregion
}