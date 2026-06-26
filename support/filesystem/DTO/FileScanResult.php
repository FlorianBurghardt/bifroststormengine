<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\filesystem\DTO;
#endregion

/**
 * Represents all matches for a single file.
 */
final class FileScanResult
{
	#region constructor
	/**
	 * @param FileMatch[] $matches
	 */
	public function __construct(
		private readonly string $file,
		private readonly array $matches
	) {}
	#endregion

	#region public methods
	public function getFile(): string
	{
		return $this->file;
	}

	/**
	 * @return FileMatch[]
	 */
	public function getMatches(): array
	{
		return $this->matches;
	}

	public function toArray(): array
	{
		return [
			'file'    => $this->file,
			'matches' => \array_map(
				static fn (FileMatch $match): array => $match->toArray(),
				$this->matches
			),
		];
	}
	#endregion
}