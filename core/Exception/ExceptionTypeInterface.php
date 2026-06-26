<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\Exception;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
#endregion

interface ExceptionTypeInterface
{
	public function getStatusCode(): HTTPStatusCode;
	public function getDefaultMessage(): string;
}