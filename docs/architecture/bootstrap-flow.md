# Bootstrap Flow

## Overview

The framework introduces a dedicated bootstrap layer to prepare all required dependencies.

## Flow

```text
Config + Environment
↓
KernelFactory
↓
Kernel
↓
HttpDispatcher
```

### Note

- The bootstrap layer does not modify the HTTP pipeline.
- It only prepares and wires dependencies.

## Responsibilities

| Layer         | Responsibility                        |
| ------------- | ------------------------------------- |
| Config        | Provides application configuration    |
| Environment   | Defines runtime environment           |
| KernelFactory | Wires dependencies and creates Kernel |
| Kernel        | Entry point                           |
| Dispatcher    | Executes request pipeline             |

---

## Implementation

```php
$factory = new KernelFactory($config, Environment::DEV);

$kernel = $factory->create(
	router: $router,
	middleware: [],
	errorHandler: $errorHandler
);
```