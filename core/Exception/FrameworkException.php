<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\Exception;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
use Exception;
use Throwable;
#endregion

class FrameworkException extends Exception
{
	#region properties
	protected HTTPStatusCode $statusCode;
	protected int $innerCode;
	protected ExceptionTypeInterface $type;
	#endregion

	#region constructor
	public function __construct(
		ExceptionTypeInterface $type,
		int $innerCode,
		?string $customMessage = null,
		?HTTPStatusCode $overrideStatus = null,
		?Throwable $previous = null
	)
	{
		$this->type = $type;
		$this->innerCode = $innerCode;
		$this->statusCode = $overrideStatus ?? $type->getStatusCode();

		$message = $customMessage ?? $type->getDefaultMessage();

		parent::__construct($message, $this->statusCode->value, $previous);
	}
	#endregion

	#region public methods
	public function getStatusCode(): HTTPStatusCode
	{
		return $this->statusCode;
	}

	public function getInnerCode(): int
	{
		return $this->innerCode;
	}

	public function getType(): ExceptionTypeInterface
	{
		return $this->type;
	}
	#endregion
}