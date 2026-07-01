<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core\bootstrap;

use de\bifroststormengine\core\bootstrap\KernelFactory;
use de\bifroststormengine\core\config\Config;
use de\bifroststormengine\core\environment\Environment;
use de\bifroststormengine\core\Exception\HttpErrorHandler;
use de\bifroststormengine\core\FrameworkManifestProvider;
use de\bifroststormengine\core\Kernel;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Routing\Route;
use de\bifroststormengine\http\Routing\RouteMatch;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\tests\fixtures\middleware\TestMiddleware;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class KernelFactoryTest extends TestKernel
{
	#region tests
	public function testFactoryCreatesKernel(): void
	{
		$config = new Config([]);
		$factory = new KernelFactory($config, Environment::DEV);

		$router = $this->createRouter();

		$kernel = $factory->create(
			router: $router,
			middleware: [],
			errorHandler: $this->createErrorHandler()
		);

		$this->assertInstanceOf(Kernel::class, $kernel);
	}

	public function testFactoryUsesConfigMiddleware(): void
	{
		$config = new Config([
			'middleware' => [
				TestMiddleware::class
			]
		]);

		$factory = new KernelFactory($config, Environment::DEV);

		$kernel = $factory->create(
			router: $this->createRouter(),
			middleware: null,
			errorHandler: $this->createErrorHandler()
		);

		$this->assertInstanceOf(Kernel::class, $kernel);
	}

	public function testFactoryOverrideMiddleware(): void
	{
		$config = new Config([
			'middleware' => [
				TestMiddleware::class
			]
		]);

		$factory = new KernelFactory($config, Environment::DEV);

		$kernel = $factory->create(
			router: $this->createRouter(),
			middleware: [],
			errorHandler: $this->createErrorHandler()
		);

		$this->assertInstanceOf(Kernel::class, $kernel);
	}
	#endregion

	#region private methods
	private function createRouter(): RouterInterface
	{
		return new class implements RouterInterface {
			public function addRoute(Route $route): void {}

			public function match(Request $request): RouteMatch
			{
				throw new \RuntimeException('Not used');
			}
		};
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