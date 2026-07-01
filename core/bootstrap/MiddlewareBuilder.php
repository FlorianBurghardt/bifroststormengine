<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\bootstrap;

use de\bifroststormengine\core\config\ConfigInterface;
use de\bifroststormengine\http\Handler\MiddlewareInterface;
#endregion

final class MiddlewareBuilder
{
	#region construct
	public function __construct(
		private readonly ConfigInterface $config
	) {}
	#endregion

	#region public methods
	/**
	 * @return MiddlewareInterface[]
	 */
	public function build(): array
	{
		$definitions = $this->config->get('middleware', []);

		if (!\is_array($definitions))
		{
			throw new \RuntimeException('Config key "middleware" must be an array');
		}

		$result = [];

		foreach ($definitions as $class)
		{
			if (!\is_string($class))
			{
				throw new \RuntimeException(
					sprintf('Middleware definition must be string, got %s', get_debug_type($class))
				);
			}

			if (!\class_exists($class))
			{
				throw new \RuntimeException(
					sprintf('Middleware class %s not found', $class)
				);
			}

			$instance = new $class();

			if (!$instance instanceof MiddlewareInterface)
			{
				throw new \RuntimeException(
					sprintf('%s must implement MiddlewareInterface', $class)
				);
			}

			$result[] = $instance;
		}

		return $result;
	}
	#endregion
}