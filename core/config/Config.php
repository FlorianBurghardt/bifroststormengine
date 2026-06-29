<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\config;
#endregion

final class Config implements ConfigInterface
{
	#region properties
	private readonly array $data;
	#endregion

	#region construct
	public function __construct(array $data)
	{
		$this->data = $data;
	}
	#endregion

	#region public methods
	public function get(string $key, mixed $default = null): mixed
	{
		return $this->data[$key] ?? $default;
	}

	public function has(string $key): bool
	{
		return \array_key_exists($key, $this->data);
	}
	#endregion
}