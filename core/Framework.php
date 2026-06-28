<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core;

use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\http\Exception\HttpExceptionResponder;
#endregion

final class Framework
{
	#region private constants
	private const NAME = 'Bifrost StormEngine';
	private const VERSION_MAJOR = 1;
	private const VERSION_MINOR = 0;
	private const VERSION_PATCH = 0;
	#endregion

	#region public constants
	public const VERSION = self::VERSION_MAJOR . '.' . self::VERSION_MINOR . '.' . self::VERSION_PATCH;
	#endregion

	#region constructor
	private function __construct() {}
	#endregion

	#region public static methods
	public static function getName(): string
	{
		return self::NAME;
	}

	public static function getVersionMajor(): int
	{
		return self::VERSION_MAJOR;
	}

	public static function getVersionMinor(): int
	{
		return self::VERSION_MINOR;
	}

	public static function getVersionPatch(): int
	{
		return self::VERSION_PATCH;
	}

	public static function getVersion(): string
	{
		return self::VERSION;
	}

	public static function createKernel(
		RouterInterface $router,
		array $middleware,
		HttpExceptionResponder $exceptionResponder
	): Kernel
	{
		return new Kernel(
			router: $router,
			middleware: $middleware,
			exceptionResponder: $exceptionResponder
		);
	}
	#endregion
}