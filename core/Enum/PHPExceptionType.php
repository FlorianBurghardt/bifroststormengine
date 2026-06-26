<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\Enum;

use de\bifroststormengine\core\Exception\ExceptionTypeInterface;
#endregion

enum PHPExceptionType implements ExceptionTypeInterface
{
	#region cases
	case LOGIC_ERROR;
	case RUNTIME_ERROR;
	#endregion

	#region public methods
	public function getDefaultMessage(): string
	{
		return match ($this)
		{
			self::LOGIC_ERROR       => 'Internal logic error',
			self::RUNTIME_ERROR     => 'Internal runtime error'
		};
	}

	public function getStatusCode(): HTTPStatusCode
	{
		return HTTPStatusCode::INTERNAL_SERVER_ERROR;
	}
	#endregion
}
