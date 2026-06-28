# Getting Started

## Minimal Working Example

```php
use de\bifroststormengine\core\Framework;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Request\Request;

$kernel = Framework::createKernel(
	router: $router,
	middleware: [],
	exceptionResponder: $exceptionResponder
);

$request = new Request(
	method: HttpMethod::GET,
	uri: '/users/1'
);

$response = $kernel->handle($request);
```

## Example Route

```php
$router->addRoute(
	new Route(
		methods: [HttpMethod::GET],
		pathPattern: '/users/{id}',
		handler: new class implements HttpHandlerInterface {
			public function handle(Request $request): Response
			{
				$match = $request->getRouteMatch();
				$id = $match->getPathParam('id');

				return new JsonResponse(['id' => $id]);
			}
		}
	)
);
```