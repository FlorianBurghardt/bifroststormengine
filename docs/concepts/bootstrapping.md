# Bootstrapping

## Overview

Bootstrapping is handled by the `KernelFactory`.

---

## Responsibilities

The factory wires together:

- Config
- Environment
- Error handling

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
		array $middleware,
		HttpErrorHandler $errorHandler
	): Kernel
}
```

## Key Behavior

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
- No dependency injection container is used

## Result

- Clean application bootstrap
- Centralized configuration point
- Strong separation of concerns
- Future extensibility enabled