<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Routing;

use de\bifroststormengine\http\Handler\HttpHandlerInterface;
#endregion

final class RouteMatch
{
	#region properties
	private array $pathParams;
	#endregion

	#region constructor
	public function __construct(
		private Route $route,
		array $pathParams = []
	)
	{
		$this->pathParams = $pathParams;
	}
	#endregion

	#region public methods
	public function getRoute(): Route
	{
		return $this->route;
	}

	public function getPathParams(): array
	{
		return $this->pathParams;
	}

	public function getPathParam(string $name, ?string $default = null): ?string
	{
		return $this->pathParams[$name] ?? $default;
	}

	public function getHandler(): HttpHandlerInterface
	{
		return $this->route->getHandler();
	}
	#endregion
}