<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\filesystem;

use de\bifroststormengine\support\filesystem\Options\FileSearchOptions;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
#endregion

/**
 * Utility class to work with files and directories.
 *
 * This class is intentionally kept dependency-free and can be used
 * by internal tools (e.g. documentation generator, structure export, etc.).
 */
final class FileSystemExplorer
{
	#region public static methods
	/**
	 * Scans a directory for files using the given options.
	 *
	 * The resulting array contains absolute file paths.
	 *
	 * @param string            $directory Base directory to scan.
	 * @param FileSearchOptions $options   Scan options.
	 *
	 * @return string[] List of absolute file paths.
	 */
	public static function scanWithOptions(string $directory, FileSearchOptions $options): array
	{
		$directory = \realpath($directory);

		if ($directory === false || !\is_dir($directory))
		{
			return [];
		}

		if ($options->isRecursive() === false)
		{
			return self::scanNonRecursive($directory, $options);
		}

		return self::scanRecursive($directory, $options);
	}

	/**
	 * Trim all paths in an array at a certain marker.
	 *
	 * Example:
	 *   trimPaths(['/var/app/src/Foo.php'], '/src/', true)
	 *   => ['Foo.php']
	 *
	 * @param string[] $paths
	 * @param string   $trimAt    Marker to trim at.
	 * @param bool     $trimAfter If true, the marker itself is removed.
	 *
	 * @return string[]
	 */
	public static function trimPaths(array $paths, string $trimAt, bool $trimAfter = false): array
	{
		$result = [];

		foreach ($paths as $path)
		{
			$pos = \strpos($path, $trimAt);

			if ($pos === false)
			{
				$result[] = $path;
				continue;
			}

			if ($trimAfter)
			{
				$pos += \strlen($trimAt);
			}

			$result[] = \substr($path, $pos);
		}
		return $result;
	}

	/**
	 * Adds a common path prefix to an array of relative paths.
	 *
	 * @param string[] $paths
	 * @param string   $prefix Base path / prefix to add.
	 *
	 * @return string[]
	 */
	public static function addPathPrefix(array $paths, string $prefix): array
	{
		$prefix = \rtrim($prefix, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		return \array_map(
			static fn (string $path): string =>
				$prefix . \ltrim($path, DIRECTORY_SEPARATOR),
			$paths
		);
	}
	#endregion

	#region private static methods
	/**
	 * @return string[]
	 */
	private static function scanNonRecursive(string $directory, FileSearchOptions $options): array
	{
		$files = [];

		foreach (\scandir($directory) ?: [] as $entry)
		{
			if ($entry === '.' || $entry === '..')
			{
				continue;
			}

			$path = $directory . DIRECTORY_SEPARATOR . $entry;

			if (!\is_file($path))
			{
				continue;
			}

			if (self::matchesOptions($path, $options))
			{
				$files[] = $path;
			}
		}
		return $files;
	}

	/**
	 * @return string[]
	 */
	private static function scanRecursive(string $directory, FileSearchOptions $options): array
	{
		$files = [];

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$directory,
				RecursiveDirectoryIterator::SKIP_DOTS
			)
		);

		/** @var SplFileInfo $fileInfo */
		foreach ($iterator as $fileInfo)
		{
			if (!$fileInfo->isFile())
			{
				continue;
			}

			$realPath = $fileInfo->getRealPath();
			if ($realPath === false)
			{
				continue;
			}

			if (self::matchesOptions($realPath, $options))
			{
				$files[] = $realPath;
			}
		}
		return $files;
	}

	private static function matchesOptions(string $filePath, FileSearchOptions $options): bool
	{
		$extensions = $options->getExtensions();
		if (!empty($extensions))
		{
			$ext = \pathinfo($filePath, PATHINFO_EXTENSION);
			if ($ext === '')
			{
				return false;
			}

			$ext = \strtolower($ext);
			$normalizedExtensions = \array_map('strtolower', $extensions);

			if (!in_array($ext, $normalizedExtensions, true))
			{
				return false;
			}
		}

		$includePatterns = $options->getIncludePatterns();
		if (!empty($includePatterns))
		{
			$matchesInclude = false;

			foreach ($includePatterns as $pattern)
			{
				if (\preg_match($pattern, $filePath) === 1)
				{
					$matchesInclude = true;
					break;
				}
			}

			if (!$matchesInclude)
			{
				return false;
			}
		}

		$excludePatterns = $options->getExcludePatterns();
		if (!empty($excludePatterns))
		{
			foreach ($excludePatterns as $pattern)
			{
				if (\preg_match($pattern, $filePath) === 1)
				{
					return false;
				}
			}
		}
		return true;
	}
	#endregion
}