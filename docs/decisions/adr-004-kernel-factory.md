# ADR-004: KernelFactory Introduction

## Status
Accepted

## Context

Bootstrapping logic needed separation from application code.

## Decision

Introduce a KernelFactory responsible for creating the Kernel.

## Rationale

- Centralized object creation
- Clean separation of concerns
- Preparation for structured dependency wiring

## Consequences

- Additional abstraction layer
- Improved maintainability
- Better testability