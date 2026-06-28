<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\filesystem\Options;
#endregion

/**
 * Options for file system scans.
 *
 * This class is intentionally kept simple and dependency-free.
 * It is immutable: all "with*" methods return a cloned instance.
 */

final class FileSearchOptions
{
	#region constructor
	/**
	 * @param bool $recursive
	 *        Whether to scan directories recursively.
	 * @param string[]|null $extensions
	 *        Optional list of file extensions (without dot, e.g. ["php", "html"]).
	 *        If null or empty, all files are allowed.
	 * @param string[]|null $includePatterns
	 *        Optional list of PCRE patterns for absolute file paths.
	 *        File must match at least one include pattern (if given).
	 * @param string[]|null $excludePatterns
	 *        Optional list of PCRE patterns for absolute file paths.
	 *        File must not match any exclude pattern.
	 */

	public function __construct(
		private bool $recursive = true,
		private ?array $extensions = null,
		private ?array $includePatterns = null,
		private ?array $excludePatterns = null,
	) {}
	#endregion

	#region public methods
	public function isRecursive(): bool
	{
		return $this->recursive;
	}

	public function withRecursive(bool $recursive): self
	{
		$clone = clone $this;
		$clone->recursive = $recursive;
		return $clone;
	}

	/**
	 * @return string[]|null
	 */
	public function getExtensions(): ?array
	{
		return $this->extensions;
	}

	/**
	 * @param string[]|null $extensions
	 */
	public function withExtensions(?array $extensions): self
	{
		$clone = clone $this;
		$clone->extensions = $extensions;
		return $clone;
	}

	/**
	 * @return string[]|null
	 */
	public function getIncludePatterns(): ?array
	{
		return $this->includePatterns;
	}

	/**
	 * @param string[]|null $includePatterns
	 */
	public function withIncludePatterns(?array $includePatterns): self
	{
		$clone = clone $this;
		$clone->includePatterns = $includePatterns;
		return $clone;
	}

	/**
	 * @return string[]|null
	 */
	public function getExcludePatterns(): ?array
	{
		return $this->excludePatterns;
	}

	/**
	 * @param string[]|null $excludePatterns
	 */
	public function withExcludePatterns(?array $excludePatterns): self
	{
		$clone = clone $this;
		$clone->excludePatterns = $excludePatterns;
		return $clone;
	}
	#endregion
}