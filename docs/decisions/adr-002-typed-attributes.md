# ADR-002: Typed Attribute Access

## Status
Accepted

## Context

Attributes were accessed via string keys and casting.

## Decision

Introduce typed access:

```php
getAttributeAs()
getRouteMatch()
```

## Consequences

- Increased type safety
- Reduced runtime errors