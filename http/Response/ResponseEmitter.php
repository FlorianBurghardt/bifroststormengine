<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Response;
#endregion

final class ResponseEmitter
{
	#region public methods
	public function emit(Response $response): void
	{
		\http_response_code($response->getStatusCode()->value);

		foreach ($response->getHeaders() as $name => $values)
		{
			foreach ($values as $value)
			{
				\header("{$name}: {$value}", false);
			}
		}

		echo $response->getBody();
	}
	#endregion
}