<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http;

use de\bifroststormengine\http\Exception\HttpExceptionResponder;
use de\bifroststormengine\http\Internal\MiddlewareChainHandler;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\http\Routing\RouteMatch;
use de\bifroststormengine\http\Handler\MiddlewareInterface;
use Throwable;
#endregion

final class HttpDispatcher
{
	#region constants
	public const ATTR_ROUTE_MATCH = RouteMatch::class;
	#endregion

	#region properties
	/** @var MiddlewareInterface[] */
	private readonly array $middleware;
	#endregion

	#region constructor
	public function __construct(
		private readonly RouterInterface $router,
		private readonly HttpExceptionResponder $exceptionResponder,
		array $middleware = [],
	)
	{
		foreach ($middleware as $m)
		{
			if (!$m instanceof MiddlewareInterface)
			{
				throw new \InvalidArgumentException(
					sprintf('Invalid middleware instance of type %s',
					get_debug_type($m))
				);
			}
		}

		$this->middleware = $middleware;
	}
	#endregion

	#region public methods
	public function dispatch(Request $request): Response
	{
		try
		{
			$routeMatch = $this->router->match($request);
			$request    = $request->withAttribute(self::ATTR_ROUTE_MATCH, $routeMatch);
			$handler    = $routeMatch->getHandler();

			if (!empty($this->middleware))
			{
				$handler = new MiddlewareChainHandler(
					finalHandler: $handler,
					middleware:   $this->middleware
				);
			}

			return $handler->handle($request);
		}
		catch (Throwable $e)
		{
			return $this->exceptionResponder->toHttpResponse($e);
		}
	}
	#endregion
}