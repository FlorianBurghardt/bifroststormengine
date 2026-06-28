<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core;

use de\bifroststormengine\core\Framework;
use de\bifroststormengine\core\Kernel;
use de\bifroststormengine\http\Exception\HttpExceptionResponder;
use de\bifroststormengine\http\Routing\Route;
use de\bifroststormengine\http\Routing\RouteMatch;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\core\Exception\HttpErrorHandler;
use de\bifroststormengine\core\FrameworkManifestProvider;
use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class FrameworkTest extends TestKernel
{
	#region tests
	public function testGetNameReturnsExpectedName(): void
	{
		$this->assertEquals(
			'Bifrost StormEngine',
			Framework::getName(),
			'Framework::getName() should return the expected framework name.'
		);
	}

	public function testGetVersionPartsReturnExpectedValues(): void
	{
		$this->assertEquals(1, Framework::getVersionMajor(), 'Major version should be 1.');
		$this->assertEquals(0, Framework::getVersionMinor(), 'Minor version should be 0.');
		$this->assertEquals(0, Framework::getVersionPatch(), 'Patch version should be 0.');
	}

	public function testGetVersionReturnsConcatenatedVersion(): void
	{
		$expected = \sprintf(
			'%d.%d.%d',
			Framework::getVersionMajor(),
			Framework::getVersionMinor(),
			Framework::getVersionPatch()
		);

		$this->assertEquals(
			$expected,
			Framework::getVersion(),
			'Framework::getVersion() should return concatenated major.minor.patch.'
		);
	}

	public function testVersionConstantMatchesGetVersion(): void
	{
		$this->assertEquals(
			Framework::VERSION,
			Framework::getVersion(),
			'Framework::VERSION constant should match Framework::getVersion().'
		);
	}

	public function testFrameworkCreatesKernel(): void
	{
		$router = $this->createMockRouter();
		$responder = $this->createResponder();

		$kernel = Framework::createKernel(
			router: $router,
			middleware: [],
			exceptionResponder: $responder
		);

		$this->assertInstanceOf(
			Kernel::class,
			$kernel,
			'Framework::createKernel() should return a Kernel instance.'
		);
	}

	public function testCreatedKernelCanHandleRequest(): void
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

		$kernel = Framework::createKernel(
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
	private function createMockRouter(): RouterInterface
	{
		return new class implements RouterInterface
		{
			public function addRoute(Route $route): void {}

			public function match(Request $request): RouteMatch
			{
				throw new \RuntimeException('Not used');
			}
		};
	}

	private function createResponder(): HttpExceptionResponder
	{
		return new HttpExceptionResponder(
			new HttpErrorHandler(
				new FrameworkManifestProvider(null)
			)
		);
	}
	#endregion
}