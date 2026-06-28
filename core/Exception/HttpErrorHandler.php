<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\core\Exception;

use de\bifroststormengine\core\DTO\FrameworkErrorDto;
use de\bifroststormengine\core\DTO\FrameworkErrorResponseDto;
use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\FrameworkManifestProvider;
use Throwable;
#endregion

class HttpErrorHandler
{
	#region constructor
	public function __construct(
		private readonly FrameworkManifestProvider $manifestProvider,
	) {}
	#endregion

	#region public static methods
	public function handle(Throwable $exception): FrameworkErrorResponseDto
	{
		$frameworkException = $this->normalizeException($exception);

		$statusCode    = $frameworkException->getStatusCode();
		$exceptionType = $frameworkException->getType();
		$innerCode     = $frameworkException->getInnerCode();

		$errorDto = new FrameworkErrorDto(
			type:       $exceptionType->name,
			message:    $frameworkException->getMessage(),
			innerCode:  $innerCode,
			httpStatus: $statusCode->value,
		);

		$manifest = $this->manifestProvider->load();

		return new FrameworkErrorResponseDto($errorDto, $manifest);
	}
	#endregion

	#region private methods
	private function normalizeException(Throwable $exception): FrameworkException
	{
		if ($exception instanceof FrameworkException) {
			return $exception;
		}

		return new FrameworkException(
			type: HTTPExceptionType::INTERNAL_ERROR,
			innerCode: 90000,
			customMessage: $exception->getMessage(),
			previous: $exception
		);
	}
	#endregion
}