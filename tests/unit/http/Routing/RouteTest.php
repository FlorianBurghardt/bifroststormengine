<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Routing;

use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Routing\Route;
use de\bifroststormengine\tests\TestKernel;
use de\bifroststormengine\core\Enum\HTTPStatusCode;
#endregion

final class RouteTest extends TestKernel
{
	#region public tests
	public function testRouteMatchesStaticPath(): void
	{
		$route = new Route(
			methods: [HttpMethod::GET],
			pathPattern: '/users',
			handler: new DummyHandler(),
			name: 'users.list'
		);

		$params = $route->matchPath('/users');
		$this->assertTrue(\is_array($params), 'Static path should matchen.');
		$this->assertEquals([], $params, 'Static path should not have Path-Parameter.');

		$params2 = $route->matchPath('/users/');
		$this->assertTrue(\is_array($params2), 'Static path with trailing slash should match.');
	}

	public function testRouteMatchesWithPathParameters(): void
	{
		$route = new Route(
			methods: [HttpMethod::GET],
			pathPattern: '/users/{id}/orders/{orderId}',
			handler: new DummyHandler()
		);

		$params = $route->matchPath('/users/123/orders/999');

		$this->assertNotNull($params, 'Path with parametern should match.');
		$this->assertEquals('123', $params['id']);
		$this->assertEquals('999', $params['orderId']);
	}

	public function testRouteDoesNotMatchDifferentPath(): void
	{
		$route = new Route(
			methods: [HttpMethod::GET],
			pathPattern: '/users/{id}',
			handler: new DummyHandler()
		);

		$params = $route->matchPath('/projects/123');
		$this->assertEquals(null, $params, 'Wrong path must not match.');
	}

	public function testRouteProvidesMetadata(): void
	{
		$handler = new DummyHandler();

		$route = new Route(
			methods: [HttpMethod::GET, HttpMethod::POST],
			pathPattern: '/users/{id}',
			handler: $handler,
			name: 'user.detail'
		);

		$this->assertEquals('/users/{id}', $route->getPathPattern());
		$this->assertEquals('user.detail', $route->getName());
		$this->assertEquals($handler, $route->getHandler());
		$this->assertEquals([HttpMethod::GET, HttpMethod::POST], $route->getMethods());
	}
	#endregion
}

#region inner classes
/**
 * Simple dummy HttpHandler for routing tests.
 */
final class DummyHandler implements HttpHandlerInterface
{
	public function handle(Request $request): Response
	{
		return new Response(HTTPStatusCode::OK);
	}
}
#endregion