<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\bootstrap;

use de\bifroststormengine\core\Kernel;
use de\bifroststormengine\core\config\ConfigInterface;
use de\bifroststormengine\core\environment\Environment;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\http\Exception\HttpExceptionResponder;
#endregion

final class KernelFactory
{
	#region construct
	public function __construct(
		private readonly ConfigInterface $config,
		private readonly Environment $env
	) {}
	#endregion

	#region public methods
	public function create(
		RouterInterface $router,
		array $middleware,
		HttpExceptionResponder $exceptionResponder
	): Kernel
	{
		// Wichtig: KEINE Logik, nur Weiterleitung
		return new Kernel(
			router: $router,
			middleware: $middleware,
			exceptionResponder: $exceptionResponder
		);
	}
	#endregion
}