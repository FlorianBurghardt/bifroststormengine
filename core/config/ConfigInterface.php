<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\config;
#endregion

interface ConfigInterface
{
	#region public methods
	public function get(string $key, mixed $default = null): mixed;

	public function has(string $key): bool;
	#endregion
}