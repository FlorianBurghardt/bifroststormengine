<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Routing;

use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Routing\Route;
use de\bifroststormengine\http\Routing\SimpleRouter;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class SimpleRouterTest extends TestKernel
{
	#region public tests
	public function testRouterMatchesRouteByPathAndMethod(): void
	{
		$router = new SimpleRouter();
		$handler = new RouterDummyHandler();

		$router->addRoute(
			new Route(
				methods: [HttpMethod::GET],
				pathPattern: '/users/{id}',
				handler: $handler
			)
		);

		$request = new Request(
			method: HttpMethod::GET,
			uri: '/users/42'
		);

		$match = $router->match($request);

		$this->assertEquals('42', $match->getPathParam('id'));
		$this->assertEquals($handler, $match->getHandler());
	}

	public function testRouterThrowsNotFoundForUnknownPath(): void
	{
		$router = new SimpleRouter();

		$request = new Request(
			method: HttpMethod::GET,
			uri: '/unknown'
		);

		try
		{
			$router->match($request);
			$this->assertTrue(false, 'A NotFound exception should have been thrown.');
		}
		catch (FrameworkException $e)
		{
			$this->assertEquals(HTTPExceptionType::NOT_FOUND, $e->getType());
			$this->assertEquals(HTTPStatusCode::NOT_FOUND, $e->getStatusCode());
		}
	}

	public function testRouterThrowsMethodNotAllowedWhenPathMatchesButMethodDoesNot(): void
	{
		$router = new SimpleRouter();

		$router->addRoute(
			new Route(
				methods: [HttpMethod::GET],
				pathPattern: '/users/{id}',
				handler: new RouterDummyHandler()
			)
		);

		$request = new Request(
			method: HttpMethod::POST,
			uri: '/users/1'
		);

		try
		{
			$router->match($request);
			$this->assertTrue(false, 'A MethodNotAllowed exception should have been thrown.');
		}
		catch (FrameworkException $e)
		{
			$this->assertEquals(HTTPExceptionType::METHOD_NOT_ALLOWED, $e->getType());
			$this->assertEquals(HTTPStatusCode::METHOD_NOT_ALLOWED, $e->getStatusCode());
		}
	}
	#endregion
}

#region inner classes
/**
 * Simple dummy HttpHandler for routing tests.
 */
final class RouterDummyHandler implements HttpHandlerInterface
{
	public function handle(Request $request): Response
	{
		return new Response(HTTPStatusCode::OK);
	}
}
#endregion