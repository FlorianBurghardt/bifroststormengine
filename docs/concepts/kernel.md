# Kernel

## Purpose

The Kernel is the central entry point of the application.

## Usage

```php
$response = $kernel->handle($request);
```

## Responsibilities

- Accept Request
- Delegate to Dispatcher
- Defines application boundary

## Important

Do NOT use HttpDispatcher directly.