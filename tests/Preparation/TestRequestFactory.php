<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\Preparation;

use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Request\Request;
#endregion

trait TestRequestFactory
{
	#region protected methods
	protected function createRequest(
		HttpMethod $method = HttpMethod::GET,
		string $uri = '/test',
		array $queryParams = [],
		array $parsedBody = [],
		?string $rawBody = null,
		array $headers = [],
		string $protocolVersion = '1.1',
		array $attributes = [],
	): Request
	{
		return new Request(
			method:          $method,
			uri:             $uri,
			queryParams:     $queryParams,
			parsedBody:      $parsedBody,
			rawBody:         $rawBody,
			headers:         $headers,
			protocolVersion: $protocolVersion,
			attributes:      $attributes,
		);
	}
	#endregion

	#region public methods
	public function setHttpMethod(HttpMethod $method): Request
	{
		return $this->createRequest();
	}
	#endregion
}