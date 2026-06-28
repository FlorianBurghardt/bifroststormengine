<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Response\Responder;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\ResponderInterface;
use de\bifroststormengine\http\Response\Response;
#endregion

final class TextResponder implements ResponderInterface
{
	#region constructor
	public function __construct(
		private readonly HTTPStatusCode $defaultStatus = HTTPStatusCode::OK
	) {}
	#endregion

	#region public methods
	public function buildResponse(Request $request, mixed $payload): Response
	{
		if (\is_object($payload) && \method_exists($payload, 'toArray'))
		{
			$payload = $payload->toArray();
		}

		if (\is_array($payload) || \is_object($payload))
		{
			$content = \print_r($payload, true);
		}
		else
		{
			$content = (string)$payload;
		}

		return new Response(
			statusCode: $this->defaultStatus,
			headers: ['Content-Type' => ['text/plain; charset=utf-8']],
			body: $content
		);
	}
	#endregion
}