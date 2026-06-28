# Architecture Overview

The Bifrost StormEngine is structured as a layered HTTP framework.

## High-Level Flow

Request → Kernel → Dispatcher → Router → Middleware → Handler → Response

## Key Components

- Kernel (Entry Point)
- HttpDispatcher (Orchestrator)
- Router (Route Matching)
- MiddlewareChainHandler (Execution)
- Request (Typed Data Access Layer)

## Design Goals

- Clear responsibility
- Strong typing
- Deterministic execution
- Extensibility