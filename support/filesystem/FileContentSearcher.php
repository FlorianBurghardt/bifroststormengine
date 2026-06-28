<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\filesystem;

use de\bifroststormengine\support\filesystem\DTO\FileMatch;
use de\bifroststormengine\support\filesystem\DTO\FileScanResult;
use de\bifroststormengine\support\filesystem\Options\FileContentSearchOptions;
#endregion

/**
 * Generic service to search file contents for keywords or patterns.
 *
 * This class is dependency-free and can be used by internal tooling.
 */
final class FileContentSearcher
{
	#region public methods
	/**
	 * Searches the given files according to the provided options.
	 *
	 * @param string[]               $filePaths
	 * @param FileContentSearchOptions $options
	 *
	 * @return FileScanResult[]
	 */
	public function searchInFiles(array $filePaths, FileContentSearchOptions $options): array
	{
		$results = [];

		foreach ($filePaths as $filePath)
		{
			if (!\is_string($filePath) || $filePath === '' || !\is_readable($filePath))
			{
				continue;
			}

			$matches = $this->searchSingleFile($filePath, $options);

			if (!empty($matches))
			{
				$results[] = new FileScanResult($filePath, $matches);
			}
		}
		return $results;
	}
	#endregion

	#region private methods
	/**
	 * @return FileMatch[]
	 */
	private function searchSingleFile(string $filePath, FileContentSearchOptions $options): array
	{
		$handle = @\fopen($filePath, 'r');
		if ($handle === false)
		{
			return [];
		}

		$found           = [];
		$lineNumber      = 0;
		$buffering       = false;
		$buffer          = '';
		$startLine       = null;
		$keywordInBuffer = null;

		$keywords = $options->getKeywords();

		while (($line = \fgets($handle)) !== false)
		{
			$lineNumber++;

			if ($buffering === false)
			{
				if ($options->skipCommentLines() && $this->isCommentLine($line))
				{
					continue;
				}

				foreach ($keywords as $keyword)
				{
					$pos = $this->findPosition($line, $keyword, $options);

					if ($pos !== null)
					{
						$buffering       = true;
						$buffer          = \substr($line, $pos);
						$startLine       = $lineNumber;
						$keywordInBuffer = $keyword;

						if ($this->stopConditionMet($buffer, $options))
						{
							$output = $this->prepareOutput($buffer, $options);

							$found[] = new FileMatch($startLine, $output);

							$buffering       = false;
							$buffer          = '';
							$startLine       = null;
							$keywordInBuffer = null;
						}
						break;
					}
				}
			}
			else
			{
				$buffer .= $line;

				if ($this->stopConditionMet($buffer, $options))
				{
					if ($keywordInBuffer !== null && !$options->useRegex())
					{
						$pos = $this->findPosition($buffer, $keywordInBuffer, $options);
						if ($pos !== null)
						{
							$buffer = \substr($buffer, $pos);
						}
					}

					$output = $this->prepareOutput($buffer, $options);

					$found[] = new FileMatch($startLine ?? $lineNumber, $output);

					$buffering       = false;
					$buffer          = '';
					$startLine       = null;
					$keywordInBuffer = null;
				}
			}
		}
		\fclose($handle);
		return $found;
	}

	private function stopConditionMet(string $buffer, FileContentSearchOptions $options): bool
	{
		$stopAt = $options->getStopAt();
		if ($stopAt === null)
		{
			return true;
		}

		if ($options->isStopAtRegex())
		{
			return \preg_match($stopAt, $buffer) === 1;
		}

		return \str_contains($buffer, $stopAt);
	}

	private function prepareOutput(string $code, FileContentSearchOptions $options): string
	{
		$output = \trim($code);

		if ($options->flattenCode())
		{
			$output = $this->flattenCode($output);
		}

		if ($options->includeComments() === false)
		{
			$output = $this->removeComments($output);
		}

		return $output;
	}

	private function flattenCode(string $code): string
	{
		$code = \str_replace(["\r", "\n", "\t"], ' ', $code);
		$code = \preg_replace('/\s+/', ' ', $code) ?? $code;
		return \trim($code);
	}

	private function removeComments(string $code): string
	{
		// remove // and # comments
		$code = \preg_replace('#//.*#', '', $code) ?? $code;
		$code = \preg_replace('#\#.*#', '', $code) ?? $code;

		// remove /* ... */ comments (multi-line)
		$code = \preg_replace('#/\*.*?\*/#s', '', $code) ?? $code;

		return \trim($code);
	}

	private function isCommentLine(string $line): bool
	{
		$trimmed = \ltrim($line);

		return \str_starts_with($trimmed, '//')
			|| \str_starts_with($trimmed, '#')
			|| \str_starts_with($trimmed, '/*')
			|| \str_starts_with($trimmed, '*')
			|| \str_starts_with($trimmed, '*/');
	}

	/**
	 * Finds the position of the keyword or pattern in the given line
	 * according to the options. Returns null if not found.
	 */
	private function findPosition(string $line, string $keyword, FileContentSearchOptions $options): ?int
	{
		if ($options->useRegex())
		{
			// We expect $keyword to be a full PCRE pattern including delimiters and modifiers,
			// e.g. '/class\s+ExampleOne/i'.

			/** @var array<int, array{0: string, 1: int}> $matches */
			$matches = [];

			if (@\preg_match($keyword, $line, $matches, PREG_OFFSET_CAPTURE) === 1)
			{
				// offset position of the whole match at index 0
				/** @var array<int, array{0: string, 1: int}> $matches */
				return $matches[0][1];
			}

			return null;
		}

		if ($options->isCaseSensitive())
		{
			$pos = \strpos($line, $keyword);
		}
		else
		{
			$pos = \stripos($line, $keyword);
		}

		return $pos === false ? null : $pos;
	}
	#endregion
}