<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Enum;

use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\core\Enum\HTTPExceptionType;
#endregion

enum HttpMethod: string
{
	#region cases
	case GET     = 'GET';
	case POST    = 'POST';
	case PUT     = 'PUT';
	case PATCH   = 'PATCH';
	case DELETE  = 'DELETE';
	case OPTIONS = 'OPTIONS';
	#endregion

	#region public static methods
	public static function fromString(string $value): self
	{
		$upper = \strtoupper($value);

		foreach (self::cases() as $case)
		{
			if ($case->value === $upper)
			{
				return $case;
			}
		}

		throw new FrameworkException(
			HTTPExceptionType::BAD_REQUEST,
			innerCode: 10100,
			customMessage: "Unsupported HTTP method: {$value}"
		);
	}

	public static function tryFromString(string $value): ?self
	{
		$upper = \strtoupper($value);

		foreach (self::cases() as $case)
		{
			if ($case->value === $upper)
			{
				return $case;
			}
		}

		return null;
	}
	#endregion

	#region public methods
	public function allowsBody(): bool
	{
		return match ($this)
		{
			self::POST, self::PUT, self::PATCH => true,
			default => false,
		};
	}
	#endregion
}