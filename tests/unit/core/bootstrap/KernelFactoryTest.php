<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\core\bootstrap;

use de\bifroststormengine\core\bootstrap\KernelFactory;
use de\bifroststormengine\core\config\Config;
use de\bifroststormengine\core\environment\Environment;
use de\bifroststormengine\core\Kernel;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\http\Exception\HttpExceptionResponder;
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
		$responder = $this->createResponder();

		$kernel = $factory->create(
			router: $router,
			middleware: [],
			exceptionResponder: $responder
		);

		$this->assertInstanceOf(Kernel::class, $kernel);
	}
	#endregion

	#region private methods
	private function createRouter(): RouterInterface
	{
		return new class implements RouterInterface {
			public function addRoute(\de\bifroststormengine\http\Routing\Route $route): void {}

			public function match(\de\bifroststormengine\http\Request\Request $request): \de\bifroststormengine\http\Routing\RouteMatch
			{
				throw new \RuntimeException('Not used');
			}
		};
	}

	private function createResponder(): HttpExceptionResponder
	{
		return new HttpExceptionResponder(
			new \de\bifroststormengine\core\Exception\HttpErrorHandler(
				new \de\bifroststormengine\core\FrameworkManifestProvider(null)
			)
		);
	}
	#endregion
}