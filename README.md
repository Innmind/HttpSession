# Http session

[![Build Status](https://github.com/Innmind/HttpSession/workflows/CI/badge.svg?branch=master)](https://github.com/Innmind/HttpSession/actions?query=workflow%3ACI)
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
    Message\Response\Response,
    Message\ServerRequest,
    Message\StatusCode,
    Headers,
    Header\SetCookie,
    Header\CookieParameter\HttpOnly,
    Header\CookieParameter\Domain,
    Header\Parameter\Parameter,
};

$manager = new Native;
$request = /* an instance of ServerRequest */

$session = $manager->start($request)->match(
    static fn($session) => $session,
    static fn() => throw new \RuntimeException('Unable to start the exception'),
);
// inject some data in the session
$manager->save($session);

$response = new Response(
    $code = StatusCode::ok,
    $request->protocolVersion(),
    Headers::of(
        SetCookie::of(
            new Parameter($session->name()->toString(), $session->id()->toString()),
            new HttpOnly,
            new Domain($request->url()->authority()->host()),
        ),
    ),
);
// send the response
```

**Note**: you should take a look at [`innmint/http-server`](https://github.com/Innmind/HttpServer) in order to know how to have access to an instance of `ServerRequest` and send the `Response`.
