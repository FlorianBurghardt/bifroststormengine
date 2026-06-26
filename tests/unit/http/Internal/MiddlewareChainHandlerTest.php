<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\unit\http\Internal;

use de\bifroststormengine\core\Enum\HTTPStatusCode;
use de\bifroststormengine\core\Enum\PHPExceptionType;
use de\bifroststormengine\core\Exception\FrameworkException;
use de\bifroststormengine\core\Framework;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Internal\MiddlewareChainHandler;
use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
use de\bifroststormengine\tests\Preparation\TestRequestFactory;
use de\bifroststormengine\tests\TestKernel;
#endregion

final class MiddlewareChainHandlerTest extends TestKernel
{
	use TestRequestFactory;

	#region public tests
	public function testMiddlewaresExecuteInCorrectOrder(): void
	{
		$trace = [];

		$middleware1  = $this->traceMiddleware($trace, 'm1');
		$middleware2  = $this->traceMiddleware($trace, 'm2');
		$finalHandler = $this->traceFinalHandler($trace);

		$handler = new MiddlewareChainHandler(
			finalHandler: $finalHandler,
			middleware:   [$middleware1, $middleware2]
		);

		$handler->handle($this->createRequest(HttpMethod::GET, '/test'));

		$this->assertEquals(
			['m1-before', 'm2-before', 'final', 'm2-after', 'm1-after'],
			$trace,
			'Middleware execution order must follow nested before/after semantics.'
		);
	}

	public function testMiddlewareCanShortCircuitChain(): void
	{
		$trace = [];

		$shortCircuit = $this->shortCircuitMiddleware($trace, 'short');
		$finalHandler = $this->traceFinalHandler($trace);

		$handler = new MiddlewareChainHandler(
			finalHandler: $finalHandler,
			middleware:   [$shortCircuit]
		);

		$res = $handler->handle($this->createRequest(HttpMethod::GET, '/test'));

		$this->assertEquals('Short', $res->getBody());
		$this->assertEquals(['short'], $trace, 'Final handler must not be called.');
	}

	public function testChainWithoutMiddlewareCallsFinalHandler(): void
	{
		$trace = [];

		$finalHandler = $this->traceFinalHandler($trace);

		$handler = new MiddlewareChainHandler(
			finalHandler: $finalHandler,
			middleware:   []
		);

		$handler->handle($this->createRequest(HttpMethod::GET, '/test'));

		$this->assertEquals(['final'], $trace);
	}

	public function testMiddlewareReceivesNextHandler(): void
	{
		$middleware = new class
		{
			public HttpHandlerInterface $nextRef;

			public function process(Request $r, HttpHandlerInterface $next): Response
			{
				$this->nextRef = $next;
				return new Response(HTTPStatusCode::OK, [], 'OK');
			}
		};

		$finalHandler = new class implements HttpHandlerInterface
		{
			public function handle(Request $r): Response
			{
				return new Response(HTTPStatusCode::OK, [], 'OK');
			}
		};

		$handler = new MiddlewareChainHandler(
			finalHandler: $finalHandler,
			middleware:   [$middleware]
		);

		$handler->handle($this->createRequest(HttpMethod::GET, '/test'));

		$this->assertInstanceOf(
			MiddlewareChainHandler::class,
			$middleware->nextRef,
			'Middleware should receive a next MiddlewareChainHandler.'
		);
	}

	public function testMiddlewareExceptionPropagates(): void
	{
		$middleware = new class
		{
			public function process(Request $r, HttpHandlerInterface $next): Response
			{
				throw new FrameworkException(
					PHPExceptionType:: RUNTIME_ERROR,
					23455,
					"fail"
				);
			}
		};

		$handler = new MiddlewareChainHandler(
			finalHandler: new class implements HttpHandlerInterface
			{
				public function handle(Request $r): Response
				{
					return new Response(HTTPStatusCode::OK, [], 'OK');
				}
			},
			middleware: [$middleware]
		);

		$this->assertThrows(
			fn() => $handler->handle($this->createRequest(HttpMethod::GET, '/test')),
			FrameworkException::class
		);
	}
	#endregion

	#region private methods
	/**
	 * Creates a middleware that records before/after execution markers into $trace.
	 */
	private function traceMiddleware(array &$trace, string $label): object
	{
		return new class($trace, $label)
		{
			public function __construct(
				private array &$trace,
				private string $label
			) {}

			public function process(Request $request, HttpHandlerInterface $next): Response
			{
				$this->trace[] = $this->label . '-before';
				$response = $next->handle($request);
				$this->trace[] = $this->label . '-after';
				return $response;
			}
		};
	}

	/**
	 * Creates a final HttpHandler that records its invocation into $trace.
	 */
	private function traceFinalHandler(array &$trace): HttpHandlerInterface
	{
		return new class($trace) implements HttpHandlerInterface
		{
			public function __construct(private array &$trace) {}

			public function handle(Request $request): Response
			{
				$this->trace[] = 'final';
				return new Response(HTTPStatusCode::OK, [], 'OK');
			}
		};
	}

	/**
	 * Creates a middleware that short-circuits the chain and records a single label in $trace.
	 */
	private function shortCircuitMiddleware(array &$trace, string $label = 'short'): object
	{
		return new class($trace, $label)
		{
			public function __construct(
				private array &$trace,
				private string $label
			) {}

			public function process(Request $request, HttpHandlerInterface $next): Response
			{
				$this->trace[] = $this->label;
				return new Response(HTTPStatusCode::CREATED, [], 'Short');
			}
		};
	}
	#endregion
}