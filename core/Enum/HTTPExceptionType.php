<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\Enum;

use de\bifroststormengine\core\Exception\ExceptionTypeInterface;
#endregion

enum HTTPExceptionType implements ExceptionTypeInterface
{
	#region cases
	case BAD_GATEWAY;
	case BAD_REQUEST;
	case CONFLICT;
	case DATABASE_CONNECTION;
	case FORBIDDEN;
	case METHOD_NOT_ALLOWED;
	case NOT_ACCEPTABLE;
	case NOT_FOUND;
	case PAYLOAD_TOO_LARGE;
	case SERVICE_UNAVAILABLE;
	case UNPROCESSABLE_ENTITY;
	case UNSUPPORTED_MEDIA_TYPE;
	case INTERNAL_ERROR;
	#endregion

	#region public methods
	public function getDefaultMessage(): string
	{
		return match ($this)
		{
			self::BAD_GATEWAY            => 'Bad gateway',
			self::BAD_REQUEST            => 'Bad Request',
			self::CONFLICT               => 'Conflict',
			self::DATABASE_CONNECTION    => 'Database connection failed',
			self::FORBIDDEN              => 'Access forbidden',
			self::METHOD_NOT_ALLOWED     => 'Method not allowed',
			self::NOT_ACCEPTABLE         => 'Not acceptable',
			self::NOT_FOUND              => 'Resource not found',
			self::PAYLOAD_TOO_LARGE      => 'Payload too large',
			self::SERVICE_UNAVAILABLE    => 'Service unavailable',
			self::UNPROCESSABLE_ENTITY   => 'Unprocessable entity',
			self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported media type',
			self::INTERNAL_ERROR         => 'Internal server error'
		};
	}

	public function getStatusCode(): HTTPStatusCode
	{
		return match ($this)
		{
			self::BAD_GATEWAY            => HTTPStatusCode::BAD_GATEWAY,
			self::BAD_REQUEST            => HTTPStatusCode::BAD_REQUEST,
			self::CONFLICT               => HTTPStatusCode::CONFLICT,
			self::DATABASE_CONNECTION    => HTTPStatusCode::SERVICE_UNAVAILABLE,
			self::FORBIDDEN              => HTTPStatusCode::FORBIDDEN,
			self::METHOD_NOT_ALLOWED     => HTTPStatusCode::METHOD_NOT_ALLOWED,
			self::NOT_ACCEPTABLE         => HTTPStatusCode::NOT_ACCEPTABLE,
			self::NOT_FOUND              => HTTPStatusCode::NOT_FOUND,
			self::PAYLOAD_TOO_LARGE      => HTTPStatusCode::PAYLOAD_TOO_LARGE,
			self::SERVICE_UNAVAILABLE    => HTTPStatusCode::SERVICE_UNAVAILABLE,
			self::UNPROCESSABLE_ENTITY   => HTTPStatusCode::UNPROCESSABLE_ENTITY,
			self::UNSUPPORTED_MEDIA_TYPE => HTTPStatusCode::UNSUPPORTED_MEDIA_TYPE,
			self::INTERNAL_ERROR         => HTTPStatusCode::INTERNAL_SERVER_ERROR
		};
	}
	#endregion
}