# Configuration

## Overview

The framework introduces a minimal configuration system.

## Interface

```php
interface ConfigInterface
{
	public function get(string $key, mixed $default = null): mixed;
	public function has(string $key): bool;
}
```

## Interface

```php
interface ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;
    public function has(string $key): bool;
}
```

## Implementation

```php
final class Config implements ConfigInterface
{
	private readonly array $data;
}
```

## Characteristics

- Immutable (readonly storage)
- Simple key-value structure
- No nested resolution logic
- No runtime mutation

## Usage

```php
$config = new Config([
	'debug' => true
]);

$debug = $config->get('debug', false);
```

## Behavior

| Case        | Result         |
| ----------- | -------------- |
| Key exists  | Value returned |
| Nissing     | Default value  |
| has(key)    | Boolean        |


## Future Use Cases

The configuration system is designed for:
- Debug mode ✅
- Feature flags
- Logging configuration
- Middleware configuration
- Routing configuration

## Design Intent

- Lightweight abstraction
- Deterministic behavior
- No global state
- Prepared for future extensions