<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Routing;

use de\bifroststormengine\http\Handler\HttpHandlerInterface;
#endregion

final class Route
{
	#region properties
	private array $methods;
	private string $pathPattern;
	private string $regexPattern;
	private array $parameterNames;
	private ?string $name;
	private HttpHandlerInterface $handler;
	#endregion

	#region constructor
	public function __construct(
		array $methods,
		string $pathPattern,
		HttpHandlerInterface $handler,
		?string $name = null
	)
	{
		$this->methods      = $methods;
		$this->pathPattern  = self::normalizePath($pathPattern);
		$this->handler      = $handler;
		$this->name         = $name;

		[$this->regexPattern, $this->parameterNames] = self::compilePattern($this->pathPattern);
	}
	#endregion

	#region public methods
	public function getMethods(): array
	{
		return $this->methods;
	}

	public function getPathPattern(): string
	{
		return $this->pathPattern;
	}

	public function getHandler(): HttpHandlerInterface
	{
		return $this->handler;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function matchPath(string $path): ?array
	{
		$path = self::normalizePath($path);

		if (!\preg_match($this->regexPattern, $path, $matches))
		{
			return null;
		}

		$params = [];
		foreach ($this->parameterNames as $paramName)
		{
			if (isset($matches[$paramName]))
			{
				$params[$paramName] = $matches[$paramName];
			}
		}
		return $params;
	}
	#endregion

	#region private static methods
	private static function normalizePath(string $path): string
	{
		$path = '/' . \ltrim($path, '/');
		return \rtrim($path, '/') ?: '/';
	}

	private static function compilePattern(string $pattern): array
	{
		$parameterNames = [];

		$segments = \explode('/', \trim($pattern, '/'));

		$regexParts = [];

		foreach ($segments as $segment)
		{

			if (\preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/', $segment, $matches))
			{
				$param = $matches[1];
				$parameterNames[] = $param;

				$regexParts[] = '(?P<' . $param . '>[^/]+)';
			}
			else
			{
				$regexParts[] = \preg_quote($segment, '~');
			}
		}

		$regex = '~^/' . \implode('/', $regexParts) . '$~';

		return [$regex, $parameterNames];
	}
	#endregion
}