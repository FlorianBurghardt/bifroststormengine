<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Response\Responder;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\ResponderInterface;
use de\bifroststormengine\http\Response\Response;
#endregion

final class JsonResponder implements ResponderInterface
{
	#region constructor
	public function __construct(
		private readonly int $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	) {}
	#endregion

	#region public methods
	public function buildResponse(Request $request, mixed $payload): Response
	{
		if (\is_object($payload) && \method_exists($payload, 'toArray'))
		{
			$payload = $payload->toArray();
		}

		$json = \json_encode($payload, $this->jsonOptions);

		if ($json === false)
		{
			$json = \json_encode(
				['error' => 'Failed to encode JSON response.'],
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			);
		}

		return new Response(
			statusCode: HTTPStatusCode::OK,
			headers: ['Content-Type' => ['application/json; charset=utf-8']],
			body: $json ?? ''
		);
	}
	#endregion
}