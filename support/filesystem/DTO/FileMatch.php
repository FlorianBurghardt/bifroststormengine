<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\support\filesystem\DTO;
#endregion

/**
 * Represents a single match inside a file.
 */
final class FileMatch
{
	#region constructor
	public function __construct(
		private readonly int $line,
		private readonly string $code
	) {}
	#endregion

	#region public methods
	public function getLine(): int
	{
		return $this->line;
	}

	public function getCode(): string
	{
		return $this->code;
	}

	public function toArray(): array
	{
		return [
			'line' => $this->line,
			'code' => $this->code,
		];
	}
	#endregion
}