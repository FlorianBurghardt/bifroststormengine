<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Routing;

use de\bifroststormengine\http\Request\Request;
#endregion

interface RouterInterface
{
	public function addRoute(Route $route): void;
	public function match(Request $request): RouteMatch;
}