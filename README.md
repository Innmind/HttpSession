# Http session

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/HttpSession/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/HttpSession/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/HttpSession/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/HttpSession/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/HttpSession/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/HttpSession/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/HttpSession/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/HttpSession/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/HttpSession/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/HttpSession/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/HttpSession/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/HttpSession/build-status/develop) |

Library to manage session for http requests.

The goal is to break the paradigm of considering the request and response as a global environment. Request and response should be delt as transiting data. The session for a request should obey this principle as well, thus the signature `Manager::start(ServerRequest): Session`.

## Installation

```sh
composer require innmind/http-session
```

## Usage

```php
use Innmind\HttpSession\Manager\Native;
use Innmind\Http\{
    Message\Response\Response,
    Message\StatusCode\StatusCode,
    Headers\Headers,
    Header\SetCookie,
    Header\CookieValue,
    Header\CookieParameter\HttpOnly,
    Header\CookieParameter\Domain,
    Header\Parameter\Parameter,
};

$manager = new Native;
$request = /* an instance of Innmind\Http\Message\ServerRequest */

$session = $manager->start($request);
// inject some data in the session
$manager->save($request);

$response = new Response(
    $code = StatusCode::of('OK'),
    $code->associatedReasonPhrase(),
    $request->protocolVersion(),
    Headers::of(
        new SetCookie(
            new CookieValue(
                new Parameter((string) $session->name(), (string) $session->id()),
                new HttpOnly,
                new Domain($request->url()->authority()->host())
            )
        )
    )
);
// send the response
```

**Note**: you should take a look at [`innmint/http-server`](https://github.com/Innmind/HttpServer) in order to know how to have access to an instance of `ServerRequest` and send the `Response`.
