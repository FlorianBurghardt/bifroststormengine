<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Exception;

use de\bifroststormengine\core\Exception\HttpErrorHandler;
use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Response\JsonResponse;
use de\bifroststormengine\http\Response\Response;
use Throwable;
#endregion

final class HttpExceptionResponder
{
	#region constructor
	public function __construct(
			private readonly HttpErrorHandler $coreErrorHandler
	) {}
	#endregion

	#region public methods
	public function toHttpResponse(Throwable $exception): Response
	{
		$dto = $this->coreErrorHandler->handle($exception);

		$array = $dto->toArray();
		$httpStatus = $dto->error->httpStatus;

		return new JsonResponse(
			data: $array,
			statusCode: HTTPStatusCode::tryFrom($httpStatus) ?? HTTPStatusCode::INTERNAL_SERVER_ERROR
		);
	}
	#endregion
}