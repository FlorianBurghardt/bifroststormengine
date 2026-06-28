# Request Lifecycle

## Flow

1. Request enters Kernel
2. Kernel calls HttpDispatcher
3. Dispatcher resolves route
4. RouteMatch is attached to Request
5. Middleware pipeline executed
6. Final handler produces Response
7. Exceptions are transformed into HTTP responses

## Detailed Flow

```php
try {
	$routeMatch = $router->match($request);

	$request = $request->withAttribute(
		RouteMatch::class,
		$routeMatch
	);

	$handler = $routeMatch->getHandler();

	if (!empty($middleware)) {
		$handler = new MiddlewareChainHandler(...);
	}

	return $handler->handle($request);
} catch (Throwable $e) {
	return $exceptionResponder->toHttpResponse($e);
}
```

## Exception Handling

```php
try {
	...
}
catch (Throwable $e) {
	return $exceptionResponder->toHttpResponse($e);
}
```