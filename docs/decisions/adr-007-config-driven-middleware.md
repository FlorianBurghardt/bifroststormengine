# ADR-007: Config-driven Middleware

## Status
Accepted

## Context

Middleware should be configurable without modifying application code.

---

## Decision

Introduce a `MiddlewareBuilder` that constructs middleware from configuration.

---

## Implementation

```php
$definitions = $config->get('middleware', []);
```
Each class is:
- Validated
- Instantiated
- Checked against MiddlewareInterface

## Behavior

- Config defines middleware list
- Invalid configuration leads to immediate exception
- Middleware can be overridden manually

## Design Principles

- No dependency injection container
- No reflection
- Deterministic execution
- No global state

## Rationale

- Predictable behavior
- Maximum transparency
- Simple mental model

## Trade-offs

- No constructor injection supported
- Reduced flexibility for complex dependency graphs
- Strong focus on simplicity and stability

## Consequences

- High reliability and safety
- Strict validation guarantees
- Clear extension point for future features (e.g. logging, feature flags)