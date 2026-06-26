<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Request;

use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class RequestTest extends TestKernel
{
	#region public tests
	public function testConstructorStoresValuesCorrectly(): void
	{
		$method = HttpMethod::POST;
		$uri    = "/users/123";

		$queryParams = ['page' => '2', 'sort' => 'desc'];
		$parsedBody  = ['name' => 'Florian', 'role' => 'Dev'];
		$rawBody     = '{"json":true}';

		$headers = [
			'Content-Type' => ['application/json'],
			'X-Custom'     => ['foobar'],
		];

		$request = new Request(
			method: $method,
			uri: $uri,
			queryParams: $queryParams,
			parsedBody: $parsedBody,
			rawBody: $rawBody,
			headers: $headers,
			attributes: ['test' => 'abc'],
			protocolVersion: '1.1'
		);

		$this->assertEquals($method, $request->getMethod());
		$this->assertEquals($uri, $request->getUri());
		$this->assertEquals($queryParams, $request->getQueryParams());
		$this->assertEquals($parsedBody, $request->getParsedBody());
		$this->assertEquals($rawBody, $request->getRawBody());
		$this->assertEquals($headers, $request->getHeaders());
		$this->assertEquals('abc', $request->getAttribute('test'));
	}

	public function testAllAggregatesQueryAndParsedBody(): void
	{
		$request = new Request(
			method: HttpMethod::GET,
			uri: "/test",
			queryParams: ['a' => '1'],
			parsedBody: ['b' => '2']
		);

		$all = $request->all();

		$this->assertEquals('1', $all['a']);
		$this->assertEquals('2', $all['b']);
		$this->assertEquals(['a' => '1', 'b' => '2'], $all);
	}

	public function testGetReturnsCorrectValue(): void
	{
		$request = new Request(
			method: HttpMethod::GET,
			uri: "/",
			queryParams: ['x' => '123'],
			parsedBody: ['y' => '456']
		);

		$this->assertEquals('123', $request->get('x'));
		$this->assertEquals('456', $request->get('y'));
		$this->assertEquals('default', $request->get('missing', 'default'));
	}

	public function testHasWorksCorrectly(): void
	{
		$request = new Request(
			method: HttpMethod::GET,
			uri: "/",
			queryParams: ['foo' => 'bar'],
			parsedBody: ['bar' => 123]
		);

		$this->assertTrue($request->has('foo'));
		$this->assertTrue($request->has('bar'));
		$this->assertFalse($request->has('unknown'));
	}

	public function testGetHeaderLineIsCaseInsensitive(): void
	{
		$request = new Request(
			method: HttpMethod::GET,
			uri: "/",
			headers: [
				'Content-Type' => ['application/json'],
				'X-Test'       => ['abc'],
			]
		);

		$this->assertEquals('application/json', $request->getHeaderLine('content-type'));
		$this->assertEquals('application/json', $request->getHeaderLine('CONTENT-TYPE'));
		$this->assertEquals('abc', $request->getHeaderLine('x-test'));
	}

	public function testGetHeaderLineReturnsNullWhenNotFound(): void
	{
		$request = new Request(
			method: HttpMethod::GET,
			uri: "/"
		);

		$this->assertEquals(null, $request->getHeaderLine('missing-header'));
	}

	public function testWithAttributeCreatesNewInstance(): void
	{
		$req1 = new Request(
			method: HttpMethod::GET,
			uri: "/",
			attributes: ['a' => 1]
		);

		$req2 = $req1->withAttribute('b', 2);

		$this->assertEquals(1, $req1->getAttribute('a'));
		$this->assertEquals(null, $req1->getAttribute('b'));

		$this->assertEquals(2, $req2->getAttribute('b'));
		$this->assertEquals(1, $req2->getAttribute('a'));
		$this->assertFalse($req1 === $req2);
	}

	public function testProtocolVersionReturnedCorrectly(): void
	{
		$request = new Request(
			method: HttpMethod::GET,
			uri: "/",
			protocolVersion: '2.0'
		);

		$this->assertEquals('2.0', $request->getProtocolVersion());
	}

	public function testEmptyBodyHandledCorrectly(): void
	{
		$request = new Request(
			method: HttpMethod::POST,
			uri: "/submit",
			parsedBody: [],
			rawBody: null
		);

		$this->assertEquals(null, $request->getRawBody());
		$this->assertEquals([], $request->getParsedBody());
	}

	public function testGetRouteMatchThrowsWhenMissing(): void
	{
		$request = new Request(
			method: HttpMethod::GET,
			uri: '/test'
		);

		$this->assertThrows(
			fn() => $request->getRouteMatch(),
			\RuntimeException::class
		);
	}
	#endregion
}