<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\Exception;

use de\bifroststormengine\tests\Enum\TestExceptionType;
use Exception;
use Throwable;
#endregion

class TestException extends Exception
{
	#region properties
	protected int $innerCode;
	protected TestExceptionType $type;
	#endregion

	#region constructor
	public function __construct(
		TestExceptionType $type,
		int $innerCode,
		string $customMessage,
		?Throwable $previous = null)
	{
		$this->type = $type;
		$this->innerCode  = $innerCode;

		parent::__construct(
			$customMessage,
			$innerCode,
			$previous
		);
	}
	#endregion

	#region public methods
	public function getInnerCode(): int
	{
		return $this->innerCode;
	}

	public function getType(): TestExceptionType
	{
		return $this->type;
	}
	#endregion
}