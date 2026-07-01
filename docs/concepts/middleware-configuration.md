# Middleware Configuration

## Overview

Middleware can be defined via configuration and is instantiated during bootstrapping.

---

## Configuration Format

```php
$config = new Config([
	'middleware' => [
		MyMiddleware::class,
		AnotherMiddleware::class
	]
]);
```

## MiddlewareBuilder

The MiddlewareBuilder is responsible for creating middleware instances from configuration.


```php
$builder = new MiddlewareBuilder($config);
$middleware = $builder->build();
```

## Behavior

- Reads middleware key from config
- Expects an array of class names
- Instantiates each class
- Validates interface compliance

## Validation Rules (Fail-Fast)

| Condition                        | Result    |
| -------------------------------- | --------- |
| Config value not array           | Exception |
| Entry not string                 | Exception |
| Class does not exist             | Exception |
| Class not implementing interface | Exception |

## Example

```php
$config = new Config([
	'middleware' => [
		TestMiddleware::class
	]
]);

$middleware = (new MiddlewareBuilder($config))->build();
```

## Design Principles

- No dependency injection container
- No reflection
- Direct instantiation via new
- Deterministic execution
- Fail-fast validation

## Result

- Configurable middleware pipeline
- Predictable behavior
- Strict validation guarantees