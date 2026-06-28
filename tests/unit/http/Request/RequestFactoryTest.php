<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Request;

use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Request\RequestFactory;
use de\bifroststormengine\tests\Preparation\TestRequestFactory;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class RequestFactoryTest extends TestKernel
{
	use TestRequestFactory;

	#region public tests
	public function testFromComponentsBuildsGetRequestWithoutBody(): void
	{
		$method       = 'GET';
		$uri          = '/users';
		$queryParams  = ['page' => '1'];
		$postData     = [];
		$rawBody      = null;
		$headersAssoc = [];

		$request = RequestFactory::fromComponents(
			method:       $method,
			uri:          $uri,
			queryParams:  $queryParams,
			postData:     $postData,
			rawBody:      $rawBody,
			headersAssoc: $headersAssoc
		);

		$this->assertEquals(HttpMethod::GET, $request->getMethod());
		$this->assertEquals($uri, $request->getUri());
		$this->assertEquals($queryParams, $request->getQueryParams());
		$this->assertEquals([], $request->getParsedBody());
		$this->assertNull($request->getRawBody());
		$this->assertEquals([], $request->getHeaders());
	}

	public function testFromComponentsParsesJsonBodyWhenContentTypeIsJson(): void
	{
		$method      = 'POST';
		$uri         = '/api/data';
		$queryParams = ['foo' => 'bar'];
		$postData    = ['will' => 'be ignored'];
		$rawBody     = '{"name":"Florian"}';

		$headersAssoc = [
			'Content-Type' => 'application/json; charset=utf-8',
			'X-Custom'     => 'abc',
		];

		$request = RequestFactory::fromComponents(
			method:       $method,
			uri:          $uri,
			queryParams:  $queryParams,
			postData:     $postData,
			rawBody:      $rawBody,
			headersAssoc: $headersAssoc
		);

		$this->assertEquals(HttpMethod::POST, $request->getMethod());
		$this->assertEquals($uri, $request->getUri());
		$this->assertEquals($queryParams, $request->getQueryParams());
		$this->assertEquals(['name' => 'Florian'], $request->getParsedBody());
		$this->assertEquals($rawBody, $request->getRawBody());

		$expectedHeaders = [
			'Content-Type' => ['application/json; charset=utf-8'],
			'X-Custom'     => ['abc'],
		];
		$this->assertEquals($expectedHeaders, $request->getHeaders());
	}

	public function testFromComponentsThrowsOnInvalidJsonBody(): void
	{
		$method       = 'POST';
		$uri          = '/api/data';
		$queryParams  = [];
		$postData     = [];
		$rawBody      = '{ invalid json ...';
		$headersAssoc = ['Content-Type' => 'application/json'];

		$this->assertThrows(
			fn () => RequestFactory::fromComponents(
				method:       $method,
				uri:          $uri,
				queryParams:  $queryParams,
				postData:     $postData,
				rawBody:      $rawBody,
				headersAssoc: $headersAssoc
			),
			FrameworkException::class,
			'Invalid JSON body must throw FrameworkException.'
		);
	}

	public function testFromComponentsUsesPostDataForNonJsonBody(): void
	{
		$method       = 'POST';
		$uri          = '/submit';
		$queryParams  = [];
		$postData     = ['foo' => 'bar', 'baz' => 'qux'];
		$rawBody      = 'foo=bar&baz=qux';
		$headersAssoc = ['Content-Type' => 'application/x-www-form-urlencoded'];

		$request = RequestFactory::fromComponents(
			method:       $method,
			uri:          $uri,
			queryParams:  $queryParams,
			postData:     $postData,
			rawBody:      $rawBody,
			headersAssoc: $headersAssoc
		);

		$this->assertEquals(HttpMethod::POST, $request->getMethod());
		$this->assertEquals($postData, $request->getParsedBody());
		$this->assertEquals($rawBody, $request->getRawBody());
	}

	public function testFromComponentsUsesPostDataWhenNoRawBody(): void
	{
		$method       = 'POST';
		$uri          = '/submit';
		$queryParams  = [];
		$postData     = ['foo' => 'bar'];
		$rawBody      = null;
		$headersAssoc = [];

		$request = RequestFactory::fromComponents(
			method:       $method,
			uri:          $uri,
			queryParams:  $queryParams,
			postData:     $postData,
			rawBody:      $rawBody,
			headersAssoc: $headersAssoc
		);

		$this->assertEquals(HttpMethod::POST, $request->getMethod());
		$this->assertEquals($postData, $request->getParsedBody());
		$this->assertNull($request->getRawBody());
	}

	public function testFromComponentsTreatsContentTypeCaseInsensitively(): void
	{
		$method       = 'POST';
		$uri          = '/api/data';
		$queryParams  = [];
		$postData     = [];
		$rawBody      = '{"key":"value"}';

		$headersAssoc = [
			'content-type' => 'application/json', // lower case on purpose
		];

		$request = RequestFactory::fromComponents(
			method:       $method,
			uri:          $uri,
			queryParams:  $queryParams,
			postData:     $postData,
			rawBody:      $rawBody,
			headersAssoc: $headersAssoc
		);

		$this->assertEquals(['key' => 'value'], $request->getParsedBody());
	}
	#endregion
}