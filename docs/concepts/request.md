# Request

## Purpose

Central data access layer of the framework.

## Typed Attribute Access

```php
$user = $request->getAttributeAs('user', User::class);
```

## RouteMatch Access

```php
$match = $request->getRouteMatch();
```

## Benefits

- No casting required
- Early error detection
- Strong typing

## Anti-Pattern

```php
$request->getAttribute('routeMatch'); // ❌
```