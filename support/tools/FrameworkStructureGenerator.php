<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\tools;

use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\Exception\FrameworkException;
#endregion

/**
 * Generates a tree-like textual representation of the framework directory structure.
 *
 * Example output:
 *
 * de\bifroststormengine
 * └── core
 * 	├── DTO
 * 	│	├── FrameworkErrorDto.php
 * 	│	└── FrameworkManifest.php
 * 	└── Enum
 * 		└── StatusCode.php
 */
final class FrameworkStructureGenerator
{
	#region properties
	/** @var string[] */
	private array $excludedDirectories;

	/** @var string[] */
	private array $excludedFiles;

	private string $rootLabel;
	#endregion

	#region constructor
	/**
	 * @param string[] $excludedDirectories Directory names to exclude (e.g. ['.git'])
	 * @param string[] $excludedFiles       Filenames to exclude (e.g. ['.gitignore'])
	 * @param string   $rootLabel           Label printed as top-level (e.g. 'de\bifroststormengine')
	 */
	public function __construct(
		array $excludedDirectories = ['.git'],
		array $excludedFiles = ['.gitignore'],
		string $rootLabel = ''
	)
	{
		$this->excludedDirectories = $excludedDirectories;
		$this->excludedFiles       = $excludedFiles;
		$this->rootLabel           = $rootLabel;
	}
	#endregion

	#region public methods
	/**
	 * Generate a formatted tree representation for the given base directory.
	 *
	 * @throws FrameworkException if base path is not a directory
	 */
	public function generate(string $basePath): string
	{
		$basePath = \rtrim($basePath, DIRECTORY_SEPARATOR);

		if (!\is_dir($basePath))
		{
			throw new FrameworkException(
				HTTPExceptionType::CONFLICT,
				11100,
				\sprintf('Base path "%s" is not a valid directory.', $basePath)
			);
		}

		$rootName = $this->rootLabel !== '' ? $this->rootLabel : \basename($basePath);

		$outputLines   = [];
		$outputLines[] = $rootName;

		$tree = $this->buildTree($basePath);

		$this->renderTree($tree, '└── ', \chr(9), $outputLines);

		return \implode(PHP_EOL, $outputLines) . PHP_EOL;
	}
	#endregion

	#region private methods
	/**
	 * Build a nested array structure: ['dirName' => [...], 'fileName.php', ...]
	 *
	 * @return array<string, mixed>|string[]
	 */
	private function buildTree(string $directory): array
	{
		$entries = \scandir($directory);

		if ($entries === false)
		{
			return [];
		}

		$directories = [];
		$files       = [];

		foreach ($entries as $entry)
		{
			if ($entry === '.' || $entry === '..')
			{
				continue;
			}

			$fullPath = $directory . DIRECTORY_SEPARATOR . $entry;

			if (\is_dir($fullPath))
			{
				if ($this->shouldExcludeDirectory($entry))
				{
					continue;
				}
				$directories[$entry] = $this->buildTree($fullPath);
			}
			elseif (\is_file($fullPath))
			{
				if ($this->shouldExcludeFile($entry))
				{
					continue;
				}
				$files[] = $entry;
			}
		}

		\ksort($directories, SORT_NATURAL | SORT_FLAG_CASE);
		\sort($files, SORT_NATURAL | SORT_FLAG_CASE);

		return \array_merge($directories, $files);
	}

	/**
	 * @param array<string, mixed>|string[] $tree
	 * @param string                        $prefixCurrent The prefix for the current root ("└── " or "├── ")
	 * @param string                        $indent        The indentation for child elements
	 * @param string[]                      $outputLines   Collected output lines
	 */
	private function renderTree(array $tree, string $prefixCurrent, string $indent, array &$outputLines): void
	{
		$entries = [];
		foreach ($tree as $key => $value)
		{
			$entries[] = ['key' => $key, 'value' => $value];
		}

		$lastIndex = \count($entries) - 1;

		foreach ($entries as $index => $entry)
		{
			$isLast    = ($index === $lastIndex);
			$connector = $isLast ? '└── ' : '├── ';

			if (\is_array($entry['value']))
			{
				$line         = $indent . $connector . $entry['key'];
				$outputLines[] = $line;

				$childIndent = $indent . ($isLast ? \chr(9) : '│' . \chr(9));

				$this->renderTree(
					$entry['value'],
					$connector,
					$childIndent,
					$outputLines
				);
			}
			else
			{
				$outputLines[] = $indent . $connector . $entry['value'];
			}
		}
	}

	private function shouldExcludeDirectory(string $directoryName): bool
	{
		return \in_array($directoryName, $this->excludedDirectories, true);
	}

	private function shouldExcludeFile(string $fileName): bool
	{
		if (\in_array($fileName, $this->excludedFiles, true))
		{
			return true;
		}
		return false;
	}
	#endregion
}