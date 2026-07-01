# ADR-006: Debug Mode Handling

## Status
Accepted

## Context

Framework needed controlled debug output.

## Decision

Debug output is enabled only when:

```php
config.debug === true AND environment !== PROD
```

## Implementation

```php
$this->debug =
    $config->get('debug', false) === true
    && $env !== Environment::PROD;
```

## Behavior

| Config Debug | Environment | Debug Active |
| ------------ | ----------- | ------------ |
| false        | any         | ❌           |
| true         | DEV         | ✅           |
| true         | TEST        | ✅           |
| true         | PROD        | ❌           |

## Debug Output

```php
{
  "debug": {
    "type": "ExceptionClass",
    "message": "...",
    "trace": [...]
  }
}
```

## Consequences

- Safe production behavior
- Debug visibility in development
- No information leakage