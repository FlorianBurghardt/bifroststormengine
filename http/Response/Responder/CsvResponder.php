<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Response\Responder;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\ResponderInterface;
use de\bifroststormengine\http\Response\Response;
#endregion

final class CsvResponder implements ResponderInterface
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

		if (!\is_array($payload))
		{
			$payload = (array)$payload;
		}

		$fp = \fopen('php://temp', 'r+');

		if ($fp === false)
		{
			return new Response(
				statusCode: HTTPStatusCode::INTERNAL_SERVER_ERROR,
				headers: ['Content-Type' => ['text/plain; charset=utf-8']],
				body: 'Failed to create CSV stream.'
			);
		}

		if (isset($payload[0]) && \is_array($payload[0]))
		{
			\fputcsv($fp, \array_keys($payload[0]));

			foreach ($payload as $row)
			{
				if (\is_array($row))
				{
					\fputcsv($fp, $row);
				}
				else
				{
					\fputcsv($fp, [(string)$row]);
				}
			}
		}
		else
		{
			$assoc = $payload;
			\fputcsv($fp, \array_keys($assoc));
			\fputcsv($fp, \array_values($assoc));
		}

		\rewind($fp);
		$content = \stream_get_contents($fp) ?: '';
		\fclose($fp);

		return new Response(
			statusCode: $this->defaultStatus,
			headers: ['Content-Type' => ['text/csv; charset=utf-8']],
			body: $content
		);
	}
	#endregion
}