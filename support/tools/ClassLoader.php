<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\tools;
#endregion

/**
 * Simple PSR-4-inspired ClassLoader without external dependencies.
 *
 * - Mapping from Namespace-Prefix => Basedirectory.
 * - Directory-Structure have to be the same as the Namespace-Structure.
 * - Can register multiple Namespace-Prefix => Basedirectory mappings.
 *
 * Example:
 * ClassLoader::register([
 *     'de\\bifroststormengine\\' => __DIR__ . '/../src',
 *     'App\\'                    => __DIR__ . '/../app',
 * ]);
 */
final class ClassLoader
{
	#region properties
	private static array $prefixes = [];
	private static string $fileExtension = '.php';
	#endregion

	#region constructor
	private function __construct() {} // Static-only utility; no instantiation allowed
	#endregion

	#region public static methods
	/**
	 * Reister different Namespace-Prefix => Basedirectory mappings.
	 *
	 * @param array<string,string> $namespacesToDirs Mapping multiple Namespace-Prefix => Basedirectory
	 * @param string $fileExtension File-Extension, default ".php"
	 */
	public static function register(array $namespacesToDirs, string $fileExtension = '.php'): void
	{
		foreach ($namespacesToDirs as $prefix => $dir)
		{
			$normalizedPrefix = \ltrim($prefix, '\\');
			$normalizedPrefix = \rtrim($normalizedPrefix, '\\') . '\\';

			$normalizedDir = \rtrim($dir, DIRECTORY_SEPARATOR);

			self::$prefixes[$normalizedPrefix] = $normalizedDir;
		}

		self::$fileExtension = $fileExtension;

		\spl_autoload_register([self::class, 'autoload']);
	}

	/**
	 * @internal Only for Tests/Debugging
	 * @return array<string,string>
	 */
	public static function getRegisteredPrefixes(): array
	{
		return self::$prefixes;
	}

	/**
	 * @internal Only for Tests/Debugging
	 */
	public static function getBaseDirForPrefix(string $prefix): ?string
	{
		$normalizedPrefix = \rtrim(\ltrim($prefix, '\\'), '\\') . '\\';

		return self::$prefixes[$normalizedPrefix] ?? null;
	}
	#endregion

	#region private static methods
	private static function autoload(string $class): void
	{
		$class = \ltrim($class, '\\');

		foreach (self::$prefixes as $prefix => $baseDir)
		{
			if (!\str_starts_with($class, $prefix))
			{
				continue;
			}

			$relativeClass = \substr($class, \strlen($prefix));
			$relativePath = \str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass);
			$file = $baseDir . DIRECTORY_SEPARATOR . $relativePath . self::$fileExtension;

			if (\is_file($file))
			{
				require_once $file;
			}
			return;
		}
	}
	#endregion
}