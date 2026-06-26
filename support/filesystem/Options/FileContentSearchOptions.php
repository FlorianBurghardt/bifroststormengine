<?php
#region
declare(strict_types=1);
namespace de\bifroststormengine\support\filesystem\Options;
#endregion

/**
 * Options for searching content inside files.
 *
 * This class is dependency-free and immutable: all "with*" methods return a cloned instance.
 */
final class FileContentSearchOptions
{
	#region constructor
	/**
	 * @param string[]      $keywords        Keywords or patterns to search for.
	 * @param bool          $caseSensitive   Whether the search is case-sensitive.
	 * @param bool          $useRegex        If true, keywords are treated as PCRE patterns.
	 * @param string|null   $stopAt          Optional string or pattern that marks the end of a match block.
	 * @param bool          $stopAtIsRegex   If true, stopAt is treated as a PCRE pattern.
	 * @param bool          $includeComments Whether comments should be kept in the result.
	 * @param bool          $flattenCode     Whether to flatten multi-line code into a single line.
	 * @param bool          $skipCommentLines If true, pure comment lines are ignored when searching.
	 */
	public function __construct(
		private array $keywords = [],
		private bool $caseSensitive = false,
		private bool $useRegex = false,
		private ?string $stopAt = null,
		private bool $stopAtIsRegex = false,
		private bool $includeComments = true,
		private bool $flattenCode = true,
		private bool $skipCommentLines = true,
	) {}
	#endregion

	#region public methods
	/**
	 * @return string[]
	 */
	public function getKeywords(): array
	{
		return $this->keywords;
	}

	/**
	 * @param string[] $keywords
	 */
	public function withKeywords(array $keywords): self
	{
		$clone = clone $this;
		$clone->keywords = $keywords;

		return $clone;
	}

	public function isCaseSensitive(): bool
	{
		return $this->caseSensitive;
	}

	public function withCaseSensitive(bool $caseSensitive): self
	{
		$clone = clone $this;
		$clone->caseSensitive = $caseSensitive;
		return $clone;
	}

	public function useRegex(): bool
	{
		return $this->useRegex;
	}

	public function withUseRegex(bool $useRegex): self
	{
		$clone = clone $this;
		$clone->useRegex = $useRegex;
		return $clone;
	}

	public function getStopAt(): ?string
	{
		return $this->stopAt;
	}

	public function withStopAt(?string $stopAt, bool $stopAtIsRegex = false): self
	{
		$clone = clone $this;
		$clone->stopAt = $stopAt;
		$clone->stopAtIsRegex = $stopAtIsRegex;
		return $clone;
	}

	public function isStopAtRegex(): bool
	{
		return $this->stopAtIsRegex;
	}

	public function includeComments(): bool
	{
		return $this->includeComments;
	}

	public function withIncludeComments(bool $includeComments): self
	{
		$clone = clone $this;
		$clone->includeComments = $includeComments;
		return $clone;
	}

	public function flattenCode(): bool
	{
		return $this->flattenCode;
	}

	public function withFlattenCode(bool $flattenCode): self
	{
		$clone = clone $this;
		$clone->flattenCode = $flattenCode;
		return $clone;
	}

	public function skipCommentLines(): bool
	{
		return $this->skipCommentLines;
	}

	public function withSkipCommentLines(bool $skipCommentLines): self
	{
		$clone = clone $this;
		$clone->skipCommentLines = $skipCommentLines;
		return $clone;
	}
	#endregion
}