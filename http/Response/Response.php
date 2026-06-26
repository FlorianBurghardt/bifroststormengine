<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Response;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
#endregion

class Response
{
	#region properties
	private array $headers;
	private string $body;
	#endregion

	#region constructor
	public function __construct(
		private HTTPStatusCode $statusCode,
		array $headers = [],
		string $body = '',
		private string $protocolVersion = '1.1',
	)
	{
		$this->headers = $headers;
		$this->body    = $body;
	}
	#endregion

	#region public methods
	public function getStatusCode(): HTTPStatusCode
	{
		return $this->statusCode;
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

	public function getBody(): string
	{
		return $this->body;
	}

	public function getProtocolVersion(): string
	{
		return $this->protocolVersion;
	}

	public function withHeader(string $name, string|array $value): self
	{
		$values = \is_array($value) ? \array_values($value) : [$value];

		$clone = clone $this;
		$clone->headers[$name] = $values;
		return $clone;
	}

	public function withBody(string $body): self
	{
		$clone = clone $this;
		$clone->body = $body;
		return $clone;
	}

	public function withStatus(HTTPStatusCode $statusCode): self
	{
		$clone = clone $this;
		$clone->statusCode = $statusCode;
		return $clone;
	}
	#endregion
}