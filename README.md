# Http session

[![CI](https://github.com/Innmind/HttpSession/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/Innmind/HttpSession/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/Innmind/HttpSession/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/HttpSession)
[![Type Coverage](https://shepherd.dev/github/Innmind/HttpSession/coverage.svg)](https://shepherd.dev/github/Innmind/HttpSession)

Library to manage session for http requests.

The goal is to break the paradigm of considering the request and response as a global environment. Request and response should be delt as transiting data. The session for a request should obey this principle as well, thus the signature `Manager::start(ServerRequest): Maybe<Session>`.

## Installation

```sh
composer require innmind/http-session
```

## Usage

```php
use Innmind\HttpSession\Manager\Native;
use Innmind\Http\{
    Response,
    Response\StatusCode,
    ServerRequest,
    Headers,
    Header\SetCookie,
    Header\SetCookie\Directive,
    Header\SetCookie\Domain,
};

$manager = Native::of();
$request = /* an instance of ServerRequest */

$session = $manager->start($request)->match(
    static fn($session) => $session,
    static fn() => throw new \RuntimeException('Unable to start the exception'),
);
// inject some data in the session
$manager->save($session);

$response = Response::of(
    StatusCode::ok,
    $request->protocolVersion(),
    Headers::of(
        SetCookie::of(
            $session->name()->toString(),
            $session->id()->toString(),
            Directive::httpOnly,
            Domain::of($request->url()->authority()->host()),
        ),
    ),
);
// send the response
```

> [!NOTE]
> You should take a look at [`innmind/http-server`](https://github.com/Innmind/HttpServer) in order to know how to have access to an instance of `ServerRequest` and send the `Response`.
