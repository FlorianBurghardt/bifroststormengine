<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core;

use de\bifroststormengine\core\config\Config;
use de\bifroststormengine\core\Kernel;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Routing\Route;
use de\bifroststormengine\http\Routing\RouteMatch;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\http\Exception\HttpExceptionResponder;
use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\core\FrameworkManifestProvider;
use de\bifroststormengine\core\Exception\HttpErrorHandler;
use de\bifroststormengine\tests\TestKernel;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\core\environment\Environment;
#endregion

final class KernelTest extends TestKernel
{
	#region public tests
	public function testKernelDispatchesRequest(): void
	{
		$handler = new class implements HttpHandlerInterface {
			public function handle(Request $request): Response
			{
				return new Response(HTTPStatusCode::OK, [], 'OK');
			}
		};

		$router = new class($handler) implements RouterInterface {
			public function __construct(private HttpHandlerInterface $handler) {}

			public function addRoute(Route $route): void {}

			public function match(Request $request): RouteMatch
			{
				return new RouteMatch(
					new Route(
						methods: [HttpMethod::GET],
						pathPattern: '/test',
						handler: $this->handler
					),
					[]
				);
			}
		};

		$responder = $this->createResponder();

		$kernel = new Kernel(
			router: $router,
			middleware: [],
			exceptionResponder: $responder
		);

		$request = new Request(
			method: HttpMethod::GET,
			uri: '/test'
		);

		$response = $kernel->handle($request);

		$this->assertEquals(HTTPStatusCode::OK, $response->getStatusCode());
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