# Routing

## Router Interface

```php
interface RouterInterface
{
    public function match(Request $request): RouteMatch;
}
```

## Route vs RouteMatch

### Route

Defines:
- Path pattern
- Handler

### RouteMatch

Contains:
- Resolved parameters
- Handler

## Example

```php
$id = $request->getRouteMatch()->getPathParam('id');
```

### Example:

/users/{id} → id = 10