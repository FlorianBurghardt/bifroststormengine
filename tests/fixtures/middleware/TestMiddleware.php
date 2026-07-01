<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\tests\fixtures\middleware;

use de\bifroststormengine\http\Handler\HttpHandlerInterface;
use de\bifroststormengine\http\Handler\MiddlewareInterface;
use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
#endregion

final class TestMiddleware implements MiddlewareInterface
{
	#region public methods
	public function process(Request $request, HttpHandlerInterface $handler): Response
	{
		return $handler->handle($request);
	}
	#endregion
}