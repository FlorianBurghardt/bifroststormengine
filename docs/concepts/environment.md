# Environment

## Overview

Defines the runtime environment of the application.

---

## Enum

```php
enum Environment: string
{
	case DEV  = 'dev';
	case TEST = 'test';
	case PROD = 'prod';
}
```

## Purpose

The environment controls runtime behavior, such as:
- Debug activation
- Environment-specific logic

## Example

```php
$env = Environment::DEV;

if ($env === Environment::PROD) {
	// Production specific behavior
}
```