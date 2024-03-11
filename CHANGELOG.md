# Changelog

## [1.6.0] - 25-02-2024

### Added
- Wrap callable for Data trait. Can now use methods "callWrap" and "callWrapIfTrue" calls in classes that use Data trait (ArrayCollection etc...)
- "Collection" type to "Types" component
- NamedCollection provider
- Provider NamedCollection containing data POST to RouteAsName

### Changed
- RouteAsNameProvider changed to NamedCollectionProvider
- Exception Injector may return an erroneous parameter via the method getParameter
- Now RouteAsName throws an exception RouteException or BaseException in case of erroneous provide
- Now method callWrapIfTrue in Data trait returned self object if condition is false

### Removed
- RouteAsNameProvider

## [1.5.0] - 01-12-2023

Stable Release?