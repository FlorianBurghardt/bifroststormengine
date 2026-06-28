<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Internal;

use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\http\Handler\MiddlewareInterface;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
#endregion

/**
 * @internal
 * Chains middleware instances to a final handler.
 */
final class MiddlewareChainHandler implements HttpHandlerInterface
{
	#region properties
	/** @var MiddlewareInterface[] */
	private readonly array $middleware;
	#endregion

	#region constructor
	public function __construct(
		private readonly HttpHandlerInterface $finalHandler,
		array $middleware,
		private readonly int $index = 0
	)
	{
		// Validation in dispatcher
		$this->middleware = $middleware;
	}
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