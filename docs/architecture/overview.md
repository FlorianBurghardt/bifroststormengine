# Architecture Overview

The Bifrost StormEngine is structured as a layered HTTP framework.

---


## High-Level Flow

```text
Request → Kernel → Dispatcher → Handler → Response
```

## Bootstrap Flow

```text
Config + Environment → MiddlewareBuilder → KernelFactory → Kernel → Dispatcher
```

## Key Components

- Kernel (Entry Point)
- KernelFactory (Bootstrap Layer)
- MiddlewareBuilder (Config-driven construction)
- HttpDispatcher (Orchestrator)
- Router (Route Matching)
- MiddlewareChainHandler (Execution)
- Request (Typed Data Access Layer)

## Key Additions

- Dedicated bootstrap layer
- Config-driven middleware pipeline
- Environment-aware behavior
- Controlled debug handling (safe for production)
- Deterministic middleware construction
- Fail-fast validation strategy

## Design Goals

- Clear responsibility boundaries
- Strong typing
- Deterministic execution
- Extensibility