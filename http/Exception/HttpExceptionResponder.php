<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Exception;

use de\bifroststormengine\core\Exception\HttpErrorHandler;
use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Response\JsonResponse;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\core\config\ConfigInterface;
use de\bifroststormengine\core\environment\Environment;
use Throwable;
#endregion

final class HttpExceptionResponder
{

	#region properties
	private readonly bool $debug;
	#endregion

	#region constructor
	public function __construct(
			private readonly HttpErrorHandler $coreErrorHandler,
			ConfigInterface $config,
			Environment $env
	)
	{

		// Debug is active only if config specifies it AND the environment is not prod
		$this->debug = $config->get('debug', false) === true
			&& $env !== Environment::PROD;

	}
	#endregion

	#region public methods
	public function toHttpResponse(Throwable $exception): Response
	{
		$dto = $this->coreErrorHandler->handle($exception);

		$array = $dto->toArray();

		// Debug extention
		if ($this->debug)
		{
			$array['debug'] = [
				'type'      => \get_class($exception),
				'message'   => $exception->getMessage(),
				'trace'     => $exception->getTrace(),
			];
		}

		$httpStatus = $dto->error->httpStatus;

		return new JsonResponse(
			data: $array,
			statusCode: HTTPStatusCode::tryFrom($httpStatus) ?? HTTPStatusCode::INTERNAL_SERVER_ERROR
		);
	}
	#endregion
}