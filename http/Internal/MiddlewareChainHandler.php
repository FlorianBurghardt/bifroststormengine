<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Internal;

use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
#endregion

/**
 * @internal
 * Verkettet Middleware-Instanzen mit einem finalen Handler.
 */
final class MiddlewareChainHandler implements HttpHandlerInterface
{
	#region constructor
	public function __construct(
		private readonly HttpHandlerInterface $finalHandler,
		private readonly array $middleware,
		private readonly int $index = 0
	) {}
	#endregion

	#region public methods
	public function handle(Request $request): Response
	{
		if (!isset($this->middleware[$this->index]))
		{
			return $this->finalHandler->handle($request);
		}

		$current = $this->middleware[$this->index];

		$next = new self(
			finalHandler: $this->finalHandler,
			middleware:   $this->middleware,
			index:        $this->index + 1
		);

		return $current->process($request, $next);
	}
	#endregion
}