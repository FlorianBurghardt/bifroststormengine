# Bootstrapping

## Overview

Bootstrapping is handled by the `KernelFactory`.

---

## Responsibilities

The factory wires together:

- Config
- Environment
- Error handling
- Middleware (via configuration)

and produces a fully configured `Kernel`.

---

## Implementation

```php
final class KernelFactory
{
	public function __construct(
		private ConfigInterface $config,
		private Environment $env
	) {}

	public function create(
		RouterInterface $router,
		?array $middleware,
		HttpErrorHandler $errorHandler
	): Kernel
}
```

## Middleware Resolution

```php
$middleware = $middleware
	?? (new MiddlewareBuilder($this->config))->build();
```
## Behavior

- If middleware is explicitly provided → it is used
- If middleware is null → it is built from config

## Responder Setup

```php
$exceptionResponder = new HttpExceptionResponder(
	coreErrorHandler: $errorHandler,
	config: $this->config,
	env: $this->env
);
```

## Important Design Decision

- The factory contains no business logic
- It only wires dependencies
- Middleware construction is delegated to a dedicated builder
- No dependency injection container is used

## Result

- Clean application bootstrap
- Config-driven middleware
- Explicit override capability
- Strong separation of concerns