# Components

## Kernel

- Application entry point
- Defines system boundary

## HttpDispatcher

- Orchestrates request pipeline
- Handles exceptions

## MiddlewareChainHandler

- Executes middleware chain
- No validation (by design)

## Router

- Matches Request to Route
- Produces RouteMatch

## Request

- Typed data container
- Provides attribute access