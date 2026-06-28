<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Request;

use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\http\Enum\HttpMethod;
#endregion

final class RequestFactory
{
	#region public static methods
	public static function fromGlobals(): Request
	{
		$serverMethod  = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		$uri           = $_SERVER['REQUEST_URI'] ?? '/';
		$queryParams   = $_GET ?? [];
		$postData      = $_POST ?? [];
		$rawBody       = \file_get_contents('php://input') ?: null;
		$headersAssoc  = \function_exists('getallheaders') ? \getallheaders() : [];

		return self::fromComponents(
			method:        $serverMethod,
			uri:           $uri,
			queryParams:   $queryParams,
			postData:      $postData,
			rawBody:       $rawBody,
			headersAssoc:  $headersAssoc,
		);
	}

	/**
	 * Testable core factory logic.
	 *
	 * @param string               $method       HTTP method as received from the environment (e.g. "GET")
	 * @param string               $uri          Request URI
	 * @param array<string,mixed>  $queryParams  Query parameters (typically $_GET)
	 * @param array<string,mixed>  $postData     POST data (typically $_POST)
	 * @param string|null          $rawBody      Raw HTTP request body
	 * @param array<string,string> $headersAssoc Associative headers (e.g. ["Content-Type" => "application/json"])
	 */
	public static function fromComponents(
		string $method,
		string $uri,
		array $queryParams,
		array $postData,
		?string $rawBody,
		array $headersAssoc
	): Request
	{
		$httpMethod = HttpMethod::fromString($method);

		if ($rawBody === '')
		{
			$rawBody = null;
		}

		$headers = [];
		foreach ($headersAssoc as $name => $value)
		{
			$headers[$name] = [$value];
		}

		$contentTypeLine = $headersAssoc['Content-Type'] ?? $headersAssoc['content-type'] ?? '';

		$parsedBody = [];

		if ($rawBody !== null && $rawBody !== '')
		{
			if (\str_contains(\strtolower($contentTypeLine), 'application/json'))
			{
				$decoded = \json_decode($rawBody, true);
				if (\json_last_error() !== JSON_ERROR_NONE || !\is_array($decoded))
				{
					throw new FrameworkException(
						HTTPExceptionType::BAD_REQUEST,
						innerCode: 10010,
						customMessage: 'Invalid JSON body'
					);
				}
				$parsedBody = $decoded;
			}
			else
			{
				$parsedBody = $postData;
			}
		}
		else
		{
			$parsedBody = $postData;
		}

		return new Request(
			method:      $httpMethod,
			uri:         $uri,
			queryParams: $queryParams,
			parsedBody:  $parsedBody,
			rawBody:     $rawBody,
			headers:     $headers
		);
	}
	#endregion
}