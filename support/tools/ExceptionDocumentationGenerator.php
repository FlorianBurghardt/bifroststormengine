<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\tools;

use de\bifroststormengine\support\filesystem\FileSystemExplorer;
use de\bifroststormengine\support\filesystem\FileContentSearcher;
use de\bifroststormengine\support\filesystem\Options\FileContentSearchOptions;
use de\bifroststormengine\support\filesystem\Options\FileSearchOptions;
#endregion

final class ExceptionDocumentationGenerator
{
	#region constructor
	public function __construct(
		private readonly FileContentSearcher $searcher = new FileContentSearcher()
	) {}
	#endregion

	#region public methods
	/**
	 * Builds documentation based on "throw new FrameworkException(...)" usages.
	 */
	public function build(string $basePath, string $baseNamespaceDir = 'de'): array
	{
		// 1) Find all PHP files
		$files = FileSystemExplorer::scanWithOptions(
			$basePath,
			(new FileSearchOptions())
				->withRecursive(true)
				->withExtensions(['php'])
		);

		if ($files === [])
		{
			return [];
		}

		// 2) Search for "throw new FrameworkException"
		$options = (new FileContentSearchOptions())
			->withKeywords(['throw new FrameworkException'])
			->withStopAt(';')
			->withFlattenCode(true)
			->withIncludeComments(false);

		$scanResults = $this->searcher->searchInFiles($files, $options);
		return $this->transform($scanResults, $baseNamespaceDir);
	}

	public function buildAsJson(string $path, string $baseDir = 'de'): string
	{
		return \json_encode(
			$this->build($path, $baseDir),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);
	}
	#endregion

	#region private methods
	private function transform(array $scanResults, string $baseDir): array
	{
		$entries = [];

		foreach ($scanResults as $result)
		{
			$file = $result->getFile();
			$namespace = $this->extractNamespace($file, $baseDir);
			$class = $this->extractClassName($file);

			foreach ($result->getMatches() as $match)
			{
				$code = $match->getCode();

				// Extract parameters from FrameworkException(...)
				$parsed = $this->parseFrameworkException($code);

				if ($parsed !== null)
				{
					$entries[] = [
						'Namespace'         => $namespace,
						'Class'             => $class,
						'Line'              => $match->getLine(),
						'ErrorType'         => $parsed['type'],
						'ErrorCode'         => $parsed['innerCode'],
						'Message'           => $parsed['message'],
						'PreviousErrorCode' => $parsed['previous'],
					];
				}
			}
		}
		return $entries;
	}

	/**
	 * Extracts type, error code, message and previous error code from
	 * a flattened "throw new FrameworkException(...)" block.
	 */
	private function parseFrameworkException(string $code): ?array
	{
		if (!\str_contains($code, 'FrameworkException'))
		{
			return null;
		}

		// Extract inside FrameworkException(...)
		if (!\preg_match('/FrameworkException\s*\((.*)\)/s', $code, $m))
		{
			return null;
		}

		$paramString = trim($m[1]);

		// Split parameters (handles quoted strings)
		$params = \preg_split(
			'/,(?=(?:[^\'"]|\'[^\']*\'|"[^"]*")*$)/',
			$paramString
		);

		$params = \array_map('trim', $params);

		// ------------------------------------------------------
		// 1) Extract ExceptionType
		// ------------------------------------------------------
		$firstParam = $params[0] ?? '';

		if (!\preg_match('/ExceptionType::(\w+)/', $firstParam, $typeMatch))
		{
			return null;
		}

		$type = $typeMatch[1];

		// ------------------------------------------------------
		// 2) Named Arguments (preferred)
		// ------------------------------------------------------

		// innerCode: X
		if (\preg_match('/innerCode\s*:\s*([0-9]+)/i', $paramString, $m2))
		{
			$innerCode = (int)$m2[1];
		}
		// positional inner code
		else
		{
			$innerCode = isset($params[1]) ? (int)\trim($params[1]) : null;
		}

		// customMessage: "..."
		if (\preg_match('/customMessage\s*:\s*(["\'])(.*?)\1/i', $paramString, $m3))
		{
			$message = $m3[2];
		}
		// positional message
		else
		{
			$message = isset($params[2]) ? \trim($params[2], "\"' ") : null;
		}

		// previous: X
		if (\preg_match('/previous(?:ErrorCode)?\s*:\s*([0-9]+)/i', $paramString, $m4))
		{
			$previous = (int)$m4[1];
		}
		// positional previous
		else
		{
			$previous = isset($params[3]) ? (int)\trim($params[3]) : null;
		}

		return [
			'type'     => $type,
			'innerCode'=> $innerCode,
			'message'  => $message,
			'previous' => $previous,
		];
	}

	// --- Helpers for namespace + class extraction -------------------------------------

	private function extractNamespace(string $filePath, string $baseDir): string
	{
		$norm = \str_replace('\\', '/', $filePath);
		$parts = \explode('/', \ltrim($norm, '/'));
		$start = \array_search($baseDir, $parts, true);

		if ($start === false)
		{
			return '';
		}

		$slice = \array_slice($parts, $start, -1);
		return \implode('\\', $slice);
	}

	private function extractClassName(string $filePath): string
	{
		return \preg_replace('/\.php$/', '', \basename($filePath));
	}
	#endregion
}