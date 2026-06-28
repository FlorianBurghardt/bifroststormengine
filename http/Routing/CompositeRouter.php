<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Routing;

use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\Enum\PHPExceptionType;
use de\bifroststormengine\http\Request\Request;
#endregion

final class CompositeRouter implements RouterInterface
{
	#region properties
	private array $routers = [];
	#endregion

	#region constructor
	public function __construct(RouterInterface ...$routers)
	{
		$this->routers = $routers;
	}
	#endregion

	#region public methods
	public function addRoute(Route $route): void
	{
		if (!isset($this->routers[0]))
		{
			throw new FrameworkException(
				PHPExceptionType::LOGIC_ERROR,
				30000,
				'No underlying router configured for CompositeRouter.');
		}

		$this->routers[0]->addRoute($route);
	}

	public function match(Request $request): RouteMatch
	{
		$lastNotFound = null;

		foreach ($this->routers as $router)
		{
			try
			{
				return $router->match($request);
			}
			catch (FrameworkException $e)
			{
				if ($e->getType() === HTTPExceptionType::NOT_FOUND)
				{
					$lastNotFound = $e;
					continue;
				}
				throw $e;
			}
		}

		if ($lastNotFound !== null)
		{
			throw $lastNotFound;
		}

		throw new FrameworkException(
			HTTPExceptionType::NOT_FOUND,
			innerCode: 20110,
			customMessage: 'No router could match the request.'
		);
	}
	#endregion
}