<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Handler;

use de\bifroststormengine\http\Request\Request;
use de\bifroststormengine\http\Response\Response;
#endregion

interface HttpHandlerInterface
{
	public function handle(Request $request): Response;
}