<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Routing;

use de\bifroststormengine\core\Enum\HTTPExceptionType;
use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\http\Routing\CompositeRouter;
use de\bifroststormengine\http\Routing\Route;
use de\bifroststormengine\http\Routing\RouteMatch;
use de\bifroststormengine\http\Routing\RouterInterface;
use de\bifroststormengine\tests\Preparation\TestRequestFactory;
use de\bifroststormengine\tests\TestKernel;
use Throwable;
#endregion

final class CompositeRouterTest extends TestKernel
{
	use TestRequestFactory;

	#region public tests
	public function testAddRouteDelegatesToFirstRouter(): void
	{
		$capturedRoutes = [];

		$router1 = $this->createRouter(
			matchResult: null,
			exception: null,
			wasCalled: $dummyCalled,
			message: 'router1.match() should not be called in testAddRouteDelegatesToFirstRouter',
			capturedRoutes: $capturedRoutes,
		);

		$router2 = $this->createRouter(
			matchResult: null,
			exception: null,
			wasCalled: $dummyCalled2,
			message: 'router2.match() should not be called in testAddRouteDelegatesToFirstRouter',
		);

		$composite = new CompositeRouter($router1, $router2);

		$route = $this->createDummyRoute('Handler should not be called in testAddRouteDelegatesToFirstRouter');

		$composite->addRoute($route);

		$this->assertEquals(1, \count($capturedRoutes));
		$this->assertEquals($route, $capturedRoutes[0]);
	}


	public function testAddRouteThrowsWhenNoUnderlyingRouter(): void
	{
		$composite = new CompositeRouter();

		$this->assertThrows(
			fn () => $composite->addRoute($this->createDummyRoute('Handler should not be called in testAddRouteThrowsWhenNoUnderlyingRouter')),
			FrameworkException::class,
			'CompositeRouter::addRoute() should throw when no underlying router is configured.'
		);
	}

	public function testMatchReturnsFirstSuccessfulMatch(): void
	{
		$request  = $this->createRequest();
		$expected = $this->createDummyRouteMatch();

		$router1Called = false;
		$router2Called = false;

		$router1 = $this->createRouter(
			matchResult: $expected,
			exception: null,
			wasCalled: $router1Called,
			message: 'router1 should successfully match in testMatchReturnsFirstSuccessfulMatch'
		);

		$router2 = $this->createRouter(
			matchResult: null,
			exception: null,
			wasCalled: $router2Called,
			message: 'router2.match() should not be called in testMatchReturnsFirstSuccessfulMatch'
		);

		$composite = new CompositeRouter($router1, $router2);

		$result = $composite->match($request);

		$this->assertTrue($router1Called, 'First router should be called.');
		$this->assertFalse($router2Called, 'Second router must not be called when first router matches.');
		$this->assertEquals($expected, $result);
	}

	public function testMatchSkipsNotFoundAndReturnsMatchFromNextRouter(): void
	{
		$request = $this->createRequest();

		$notFoundException = $this->createFrameworkException(
			HTTPExceptionType::NOT_FOUND,
			innerCode: 1,
			customMessage: 'First router did not find a match.'
		);

		$expectedMatch = $this->createDummyRouteMatch();

		$router1Called = false;
		$router2Called = false;

		$router1 = $this->createRouter(
			matchResult: null,
			exception: $notFoundException,
			wasCalled: $router1Called,
			message: 'router1 should throw NOT_FOUND in testMatchSkipsNotFoundAndReturnsMatchFromNextRouter'
		);

		$router2 = $this->createRouter(
			matchResult: $expectedMatch,
			exception: null,
			wasCalled: $router2Called,
			message: 'router2 should match in testMatchSkipsNotFoundAndReturnsMatchFromNextRouter'
		);

		$composite = new CompositeRouter($router1, $router2);

		$result = $composite->match($request);

		$this->assertTrue($router1Called, 'First router should be called.');
		$this->assertTrue($router2Called, 'Second router should be called after NOT_FOUND from first.');
		$this->assertEquals($expectedMatch, $result);
	}

	public function testMatchPropagatesNonNotFoundFrameworkException(): void
	{
		$request = $this->createRequest();

		$internalError = $this->createFrameworkException(
			HTTPExceptionType::BAD_REQUEST, // Non-NOT_FOUND type
			innerCode: 2,
			customMessage: 'Bad request in router.'
		);

		$router1Called = false;
		$router2Called = false;

		$router1 = $this->createRouter(
			matchResult: null,
			exception: $internalError,
			wasCalled: $router1Called,
			message: 'router1 should throw BAD_REQUEST in testMatchPropagatesNonNotFoundFrameworkException'
		);

		$router2 = $this->createRouter(
			matchResult: $this->createDummyRouteMatch(),
			exception: null,
			wasCalled: $router2Called,
			message: 'router2.match() should not be called in testMatchPropagatesNonNotFoundFrameworkException'
		);

		$composite = new CompositeRouter($router1, $router2);

		$this->assertThrows(
			fn () => $composite->match($request),
			FrameworkException::class,
			'Non-NOT_FOUND FrameworkException should be propagated.'
		);

		$this->assertTrue($router1Called, 'First router should be called.');
		$this->assertFalse($router2Called, 'Second router must not be called when first throws non-NOT_FOUND exception.');
	}

	public function testMatchRethrowsLastNotFoundException(): void
	{
		$request = $this->createRequest();

		$notFound1 = $this->createFrameworkException(
			HTTPExceptionType::NOT_FOUND,
			innerCode: 3,
			customMessage: 'First not found.'
		);

		$notFound2 = $this->createFrameworkException(
			HTTPExceptionType::NOT_FOUND,
			innerCode: 4,
			customMessage: 'Second not found.'
		);

		$router1Called = false;
		$router2Called = false;

		$router1 = $this->createRouter(
			matchResult: null,
			exception: $notFound1,
			wasCalled: $router1Called,
			message: 'router1 should throw NOT_FOUND in testMatchRethrowsLastNotFoundException'
		);

		$router2 = $this->createRouter(
			matchResult: null,
			exception: $notFound2,
			wasCalled: $router2Called,
			message: 'router2 should throw NOT_FOUND in testMatchRethrowsLastNotFoundException'
		);

		$composite = new CompositeRouter($router1, $router2);

		try
		{
			$composite->match($request);
			$this->fail('Expected FrameworkException to be thrown.');
		}
		catch (FrameworkException $e)
		{
			$this->assertTrue($router1Called, 'First router should be called.');
			$this->assertTrue($router2Called, 'Second router should be called as well.');
			$this->assertEquals(
				HTTPExceptionType::NOT_FOUND,
				$e->getType(),
				'CompositeRouter should rethrow a NOT_FOUND exception.'
			);
			$this->assertTrue(
				$e === $notFound2,
				'CompositeRouter should rethrow the last NOT_FOUND exception encountered (from router2).'
			);
		}
	}

	public function testMatchThrowsNotFoundWhenNoRoutersConfigured(): void
	{
		$request   = $this->createRequest();
		$composite = new CompositeRouter();

		try
		{
			$composite->match($request);
			$this->fail('Expected FrameworkException of type NOT_FOUND to be thrown when no routers are configured.');
		}
		catch (FrameworkException $e)
		{
			$this->assertEquals(
				HTTPExceptionType::NOT_FOUND,
				$e->getType(),
				'CompositeRouter should throw NOT_FOUND when no router can match the request.'
			);
		}
	}
	#endregion

	#region private helper methods
	/**
	 * Creates a dummy Route instance for testing purposes.
	 * The handler MUST NOT be called in these tests.
	 */
	private function createDummyRoute(string $message = ''): Route
	{
		$handler = new class($message) implements HttpHandlerInterface
		{
			public function __construct(private string $message = '') {}

			public function handle(Request $request): Response
			{
				$msg = $this->message !== ''
					? $this->message
					: 'Dummy route handler should not be called in CompositeRouterTest.';
				throw new FrameworkException(HTTPExceptionType::CONFLICT, 53221, $msg);
			}
		};

		return new Route(
			methods:     [HttpMethod::GET],
			pathPattern: '/test',
			handler:     $handler,
			name:        'dummy.route'
		);
	}

	/**
	 * Creates a dummy RouteMatch instance for testing purposes.
	 */
	private function createDummyRouteMatch(): RouteMatch
	{
		$route      = $this->createDummyRoute('Handler should not be called in createDummyRouteMatch');
		$pathParams = ['id' => '123'];

		return new RouteMatch($route, $pathParams);
	}

	/**
	 * Generic configurable fake router for tests.
	 *
	 * @param RouteMatch|null $matchResult    RouteMatch to return (if no exception).
	 * @param Throwable|null $exception      Exception to throw from match(), if set.
	 * @param bool|null       $wasCalled      Optional reference flag to track if match() was invoked.
	 * @param string          $message        Custom message for internal RuntimeException if misconfigured.
	 * @param array|null      $capturedRoutes Optional reference to store captured routes.
	 */
	private function createRouter(
		?RouteMatch $matchResult = null,
		?Throwable $exception = null,
		?bool &$wasCalled = null,
		string $message = '',
		?array &$capturedRoutes = null,
	): RouterInterface
	{
		return new class($matchResult, $exception, $wasCalled, $message, $capturedRoutes) implements RouterInterface
		{
			public function __construct(
				private ?RouteMatch $matchResult,
				private ?Throwable $exception,
				private ?bool &$wasCalled,
				private string $message = '',
				private ?array &$capturedRoutes = null,
			) {}

			public function addRoute(Route $route): void
			{
				if ($this->capturedRoutes !== null)
				{
					$this->capturedRoutes[] = $route;
				}
			}

			public function match(Request $request): RouteMatch
			{
				if ($this->wasCalled !== null)
				{
					$this->wasCalled = true;
				}

				if ($this->exception !== null)
				{
					throw $this->exception;
				}

				if ($this->matchResult !== null)
				{
					return $this->matchResult;
				}

				$msg = $this->message !== ''
					? $this->message
					: 'Router misconfigured: must return match or throw exception.';
				throw new FrameworkException(HTTPExceptionType::CONFLICT, 53222, $msg);
			}
		};
	}

	private function createFrameworkException(
		HTTPExceptionType $type,
		int $innerCode = 0,
		?string $customMessage = null
	): FrameworkException
	{
		return new FrameworkException(
			type: $type,
			innerCode: $innerCode,
			customMessage: $customMessage
		);
	}
	#endregion
}