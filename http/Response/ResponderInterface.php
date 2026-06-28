<?php
#region usings
declare(strict_types=1);
namespace de\bifroststormengine\http\Response;

use de\bifroststormengine\http\Request\Request;
#endregion

interface ResponderInterface
{
	public function buildResponse(Request $request, mixed $payload): Response;
}