<?php
#region usings
declare(strict_types=1);

namespace de\bifroststormengine\core;

use de\bifroststormengine\http\HttpDispatcher;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\http\Exception\HttpExceptionResponder;
use de\bifroststormengine\http\Handler\MiddlewareInterface;
#endregion

final class Kernel
{
	#region properties
	private readonly HttpDispatcher $dispatcher;
	/** @var MiddlewareInterface[] */
	private readonly array $middleware;
	#endregion

	#region construct

	public function __construct(
		RouterInterface $router,
		array $middleware,
		HttpExceptionResponder $exceptionResponder
	)
	{
		foreach ($middleware as $m)
		{
			if (!$m instanceof MiddlewareInterface)
			{
				throw new \InvalidArgumentException(
					sprintf('Invalid middleware instance of type %s', get_debug_type($m))
				);
			}
		}

		$this->middleware = $middleware;

		$this->dispatcher = new HttpDispatcher(
			router: $router,
			middleware: $this->middleware,
			exceptionResponder: $exceptionResponder
		);
	}
	#endregion

	#region public methods
	public function handle(Request $request): Response
	{
		return $this->dispatcher->dispatch($request);
	}
	#endregion
}