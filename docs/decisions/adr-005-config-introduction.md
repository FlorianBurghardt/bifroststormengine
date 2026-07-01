# ADR-005: Configuration System

## Status
Accepted

## Context

Framework lacked a structured configuration system.

## Decision

Introduce:

- ConfigInterface
- Immutable Config implementation

## Rationale

- Provide consistent configuration access
- Maintain deterministic behavior
- Prevent runtime mutation

## Consequences

- Simple API
- No complex resolution logic
- Flexible extension point