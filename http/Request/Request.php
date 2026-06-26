<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Request;

use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\HttpDispatcher;
use de\bifroststormengine\http\Routing\RouteMatch;
#endregion

final class Request
{
	#region properties
	private array $queryParams;
	private array $parsedBody;
	private array $headers;
	private array $attributes;
	#endregion

	#region constructor
	public function __construct(
		private HttpMethod $method,
		private string $uri,
		array $queryParams = [],
		array $parsedBody = [],
		private ?string $rawBody = null,
		array $headers = [],
		private string $protocolVersion = '1.1',
		array $attributes = [],
	)
	{
		$this->queryParams = $queryParams;
		$this->parsedBody  = $parsedBody;
		$this->headers     = $headers;
		$this->attributes  = $attributes;
	}
	#endregion

	#region public methods
	public function getMethod(): HttpMethod
	{
		return $this->method;
	}

	public function getUri(): string
	{
		return $this->uri;
	}

	public function getQueryParams(): array
	{
		return $this->queryParams;
	}

	public function getQueryParam(string $key, ?string $default = null): ?string
	{
		return $this->queryParams[$key] ?? $default;
	}

	public function getParsedBody(): array
	{
		return $this->parsedBody;
	}

	public function getRawBody(): ?string
	{
		return $this->rawBody;
	}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	public function getHeaderLine(string $name): ?string
	{
		$needle = \strtolower($name);

		foreach ($this->headers as $key => $values)
		{
			if (\strtolower($key) === $needle)
			{
				return \implode(', ', $values);
			}
		}

		return null;
	}

	public function all(): array
	{
		return [...$this->queryParams, ...$this->parsedBody];	// Body overrides Query by key collision
	}

	public function has(string $key): bool
	{
		return \array_key_exists($key, $this->all());
	}

	public function get(string $key, mixed $default = null): mixed
	{
		$all = $this->all();
		return $all[$key] ?? $default;
	}

	public function getAttributes(): array
	{
		return $this->attributes;
	}

	public function getAttribute(string $name, mixed $default = null): mixed
	{
		return $this->attributes[$name] ?? $default;
	}

	/**
	 * Typed Access for Attributes.
	 *
	 * @template T
	 * @param class-string<T> $type
	 * @param string $name
	 * @return T
	 */
	public function getAttributeAs(string $name, string $type): object
	{
		$value = $this->attributes[$name] ?? null;

		if (!$value instanceof $type)
		{
			throw new \RuntimeException(
				sprintf(
					'Attribute "%s" is not of expected type %s. Actual: %s',
					$name,
					$type,
					get_debug_type($value)
				)
			);
		}

		return $value;
	}

	public function withAttribute(string $name, mixed $value): self
	{
		$clone = clone $this;
		$clone->attributes[$name] = $value;
		return $clone;
	}

	public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	public function getRouteMatch(): RouteMatch
	{
		$value = $this->attributes[HttpDispatcher::ATTR_ROUTE_MATCH] ?? null;

		if (!$value instanceof RouteMatch)
		{
			throw new \RuntimeException('RouteMatch attribute missing or invalid.');
		}

		return $value;
	}
	#endregion
}