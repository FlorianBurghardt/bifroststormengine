# Getting Started

## Minimal Working Example

```php
use de\bifroststormengine\core\config\Config;
use de\bifroststormengine\core\bootstrap\KernelFactory;
use de\bifroststormengine\core\environment\Environment;
use de\bifroststormengine\core\Exception\HttpErrorHandler;
use de\bifroststormengine\http\Enum\HttpMethod;
use de\bifroststormengine\http\Request\Request;

$config = new Config([
	'debug' => true
]);

$factory = new KernelFactory(
	config: $config,
	env: Environment::DEV
);

$kernel = $factory->create(
	router: $router,
	middleware: [], // explicit override
	errorHandler: $errorHandler
);

$request = new Request(
	method: HttpMethod::GET,
	uri: '/users/1'
);

$response = $kernel->handle($request);
```

## Config-driven Middleware

```php
$config = new Config([
    'middleware' => [
        TestMiddleware::class
    ]
]);

$kernel = $factory->create(
    router: $router,
    middleware: null, // use config
    errorHandler: $errorHandler
);
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