# ADR-003: Dual Validation of Middleware

## Status
Accepted

## Context

Middleware must always be valid.

## Decision

Validate middleware in:

- Kernel
- HttpDispatcher

## Rationale

- Defensive programming
- Fail-fast behavior

## Consequences

- Slight redundancy
- Increased robustness