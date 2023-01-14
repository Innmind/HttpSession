<?php
declare(strict_types = 1);

namespace Innmind\HttpSession\Manager;

use Innmind\HttpSession\{
    Manager,
    Session,
    Session\Id,
    Session\Name,
};
use Innmind\Http\{
    Message\ServerRequest,
    Header\Cookie,
    Header\CookieValue,
};
use Innmind\Url\Path;
use Innmind\Immutable\{
    Map,
    Sequence,
    Maybe,
    SideEffect,
};

final class Native implements Manager
{
    private ?Session\Id $session = null;

    private function __construct(Path $save = null)
    {
        if ($save instanceof Path) {
            $_ = \session_save_path($save->toString());
        }
    }

    public static function of(Path $save = null): self
    {
        return new self($save);
    }

    public function start(ServerRequest $request): Maybe
    {
        if ($this->session instanceof Session\Id) {
            /** @var Maybe<Session> */
            return Maybe::nothing();
        }

        $this->configureSessionId($request);

        if (\session_start(['use_cookies' => false]) === false) {
            /** @var Maybe<Session> */
            return Maybe::nothing();
        }

        /** @var Map<string, mixed> */
        $values = Map::of();

        /**
         * @var string $key
         * @var mixed $value
         */
        foreach ($_SESSION as $key => $value) {
            $values = ($values)($key, $value);
        }

        return Maybe::all(Id::maybe(\session_id()), Name::maybe(\session_name()))
            ->map(static fn(Id $id, Name $name) => Session::of($id, $name, $values))
            ->map(function($session) {
                $this->session = $session->id();

                return $session;
            });
    }

    public function save(Session $session): Maybe
    {
        if ($this->session !== $session->id()) {
            /** @var Maybe<SideEffect> */
            return Maybe::nothing();
        }

        $_ = $session
            ->values()
            ->foreach(static function(string $key, $value): void {
                /** @psalm-suppress MixedAssignment */
                $_SESSION[$key] = $value;
            });

        if (\session_write_close() === false) {
            /** @var Maybe<SideEffect> */
            return Maybe::nothing();
        }

        $this->session = null;
        $_SESSION = [];

        return Maybe::just(new SideEffect);
    }

    public function close(Session $session): Maybe
    {
        if ($this->session !== $session->id()) {
            /** @var Maybe<SideEffect> */
            return Maybe::nothing();
        }

        if (\session_destroy() === false) {
            /** @var Maybe<SideEffect> */
            return Maybe::nothing();
        }

        $this->session = null;

        return Maybe::just(new SideEffect);
    }

    private function configureSessionId(ServerRequest $request): void
    {
        $cookie = $request->headers()->find(Cookie::class)->match(
            static fn($cookie) => $cookie,
            static fn() => null,
        );

        if (!$cookie instanceof Cookie) {
            return;
        }

        $sessionName = \session_name();
        /** @var Sequence<CookieValue> */
        $values = Sequence::of(...$cookie->values()->toList());
        $_ = $values
            ->flatMap(static fn($value) => $value->parameters()->values())
            ->find(static fn($parameter) => $parameter->name() === $sessionName)
            ->match(
                static fn($parameter) => \session_id($parameter->value()),
                static fn() => null,
            );
    }
}
