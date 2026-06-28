<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Routing;

use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Request\Request;
#endregion

final class SimpleRouter implements RouterInterface
{
	#region properties
	private array $routes = [];
	#endregion

	#region public methods
	public function addRoute(Route $route): void
	{
		$this->routes[] = $route;
	}

	public function match(Request $request): RouteMatch
	{
		$requestMethod = $request->getMethod();
		$requestPath   = $this->extractPath($request->getUri());

		$pathMatched = false;
		$allowedMethods = [];

		foreach ($this->routes as $route)
		{
			$pathParams = $route->matchPath($requestPath);

			if ($pathParams === null)
			{
				continue;
			}

			$pathMatched = true;

			if ($this->methodAllowed($requestMethod, $route->getMethods()))
			{
				$match = new RouteMatch($route, $pathParams);
				return $match;
			}

			$allowedMethods = \array_merge($allowedMethods, $route->getMethods());
		}

		if ($pathMatched)
		{
			$allowed = \implode(
				', ',
				\array_unique(\array_map(static fn(HttpMethod $m) => $m->value, $allowedMethods))
			);

			throw new FrameworkException(
				HTTPExceptionType::METHOD_NOT_ALLOWED,
				innerCode: 20101,
				customMessage: "Method {$requestMethod->value} not allowed. Allowed: {$allowed}"
			);
		}

		throw new FrameworkException(
			HTTPExceptionType::NOT_FOUND,
			innerCode: 20100,
			customMessage: "No route matched for {$requestPath}"
		);
	}
	#endregion

	#region private methods
	private function extractPath(string $uri): string
	{
		$path = \parse_url($uri, PHP_URL_PATH) ?: '/';
		$path = '/' . \ltrim($path, '/');
		return \rtrim($path, '/') ?: '/';
	}

	private function methodAllowed(HttpMethod $method, array $allowedMethods): bool
	{
		foreach ($allowedMethods as $allowed)
		{
			if ($allowed === $method)
			{
				return true;
			}
		}
		return false;
	}
	#endregion
}