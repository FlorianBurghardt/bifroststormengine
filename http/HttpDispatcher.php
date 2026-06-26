<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http;

use de\bifroststormengine\http\Exception\HttpExceptionResponder;
use de\bifroststormengine\http\Internal\MiddlewareChainHandler;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Routing\RouterInterface;
use Throwable;
#endregion

final class HttpDispatcher
{
	#region constants
	public const ATTR_ROUTE_MATCH = 'routeMatch';
	#endregion

	#region constructor
	public function __construct(
		private readonly RouterInterface $router,
		private readonly HttpExceptionResponder $exceptionResponder,
		private readonly array $middleware = [],
	) {}
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