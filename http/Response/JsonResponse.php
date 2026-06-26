<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Response;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
#endregion

final class JsonResponse extends Response
{
	#region constructor
	public function __construct(
		array $data,
		HTTPStatusCode $statusCode = HTTPStatusCode::OK,
		array $headers = [],
		string $protocolVersion = '1.1',
		int $jsonEncodeOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
	)
	{
		$json = \json_encode($data, $jsonEncodeOptions);

		if ($json === false)
		{
			$json = \json_encode(
				['error' => 'Failed to encode JSON response.'],
				JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
			);
		}

		$headers['Content-Type'] = ['application/json; charset=utf-8'];

		parent::__construct(
			statusCode: $statusCode,
			headers: $headers,
			body: $json ?? '',
			protocolVersion: $protocolVersion
		);
	}
	#endregion
}