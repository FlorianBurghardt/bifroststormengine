# Bifrost StormEngine

Bifrost StormEngine is a lightweight, zero-dependency PHP framework
focused on deterministic request handling, clear architecture, and strong typing.

---

## Goals

- Zero Dependency
- Deterministic Architecture
- Strong Core Isolation
- High Performance
- Clear APIs
- Maintainability and Extensibility

---

## 📘 Documentation

Full documentation available here:

👉 [View Documentation](./docs/README.md)

---

## Core Principles

### Zero Dependency
No external libraries are used.

### Core Isolation
Core modules are independent and cannot be modified by extensions.

### Deterministic Architecture
Same input always produces the same output.

### No Global State
All data flows through explicit interfaces.

---

## Core Usage Rules

- Always use the **Kernel** as entry point
- Never use `HttpDispatcher` directly
- Do not use string keys for attributes
- Use `getRouteMatch()` and `getAttributeAs()`
- Middleware must implement `MiddlewareInterface`

---

## Getting Started

See:
👉 `./getting-started/getting-started.md`

---

## Documentation Structure

- Architecture → `./architecture/overview.md`
- Request Lifecycle → `./architecture/request-lifecycle.md`
- Concepts → `./concepts/`
- Decisions (ADR) → `./decisions/`