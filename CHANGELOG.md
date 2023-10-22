# Changelog

## [Unreleased]

### Removed

- Support for PHP `8.1`

## 3.2.0 - 2023-09-23

### Added

- Support for `innmind/immutable:~5.0`

## 3.1.0 - 2023-01-29

### Added

- Support for `innmind/http:~6.0`

## 3.0.0 - 2023-01-14

### Added

- `Innmind\HttpSession\Session::maybe()`

### Changed

- `Innmind\HttpSession\Manager\Native` constructor is now private, use `::of()` named constructor instead
- `Innmind\HttpSession\Session` is now immutable
- `Innmind\HttpSession\Session\Id` is now immutable
- `Innmind\HttpSession\Session\Name` is now immutable
- `Innmind\HttpSession\Sesssion::set()` has been renamed to `::with()`
- `Innmind\HttpSession\Manager::start()` now returns `Innmind\Immutable\Maybe<Innmind\HttpSession\Session>` instead of throwing
- `Innmind\HttpSession\Manager::save()`
    - now expects the `Session` instead of the `Innmind\Http\Message\ServerRequest`
    - now returns `Innmind\Immutable\Maybe<Innmind\Immutable\SideEffect>` instead of throwing
- `Innmind\HttpSession\Manager::close()`
    - now expects the `Session` instead of the `Innmind\Http\Message\ServerRequest`
    - now returns `Innmind\Immutable\Maybe<Innmind\Immutable\SideEffect>` instead of throwing
- Require php 8.1

### Removed

- `Innmind\HttpSession\Manager::get()`
- `Innmind\HttpSession\Manager::contains()`
