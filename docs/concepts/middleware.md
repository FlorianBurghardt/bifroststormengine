# Middleware

## Interface

```php
interface MiddlewareInterface
{
	public function process(Request $request, HttpHandlerInterface $handler): Response;
}
```

## Behavior

- Can modify Request or Response
- Wraps next handler
- Can stop execution (short circuit)

## Execution Order

m1-before
  m2-before
	handler
  m2-after
m1-after

## Validation (Architectural Decision)

Middleware is validated in:

1. Kernel
2. HttpDispatcher

## Why?

- Fail-fast behavior
- Defensive design
- Protection against invalid configuration

## Important

- MiddlewareChainHandler does NOT validate (Single Responsibility).

## Execution

```php
return $current->process($request, $next);
```