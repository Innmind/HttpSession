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
    ServerRequest,
    Header\Cookie,
};
use Innmind\Validation\Is;
use Innmind\Url\Path;
use Innmind\Immutable\{
    Map,
    Maybe,
    SideEffect,
};

final class Native implements Manager
{
    private ?Id $session = null;

    private function __construct(?Path $save = null)
    {
        if ($save instanceof Path) {
            $_ = \session_save_path($save->toString());
        }
    }

    #[\NoDiscard]
    public static function of(?Path $save = null): self
    {
        return new self($save);
    }

    #[\Override]
    public function start(ServerRequest $request): Maybe
    {
        if ($this->session instanceof Id) {
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

        return Maybe::all(
            Maybe::just(\session_id())
                ->keep(Is::string()->asPredicate())
                ->flatMap(Id::maybe(...)),
            Maybe::just(\session_name())
                ->keep(Is::string()->asPredicate())
                ->flatMap(Name::maybe(...)),
        )
            ->map(static fn(Id $id, Name $name) => Session::of($id, $name, $values))
            ->map(function($session) {
                $this->session = $session->id();

                return $session;
            });
    }

    #[\Override]
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

        return Maybe::just(SideEffect::identity);
    }

    #[\Override]
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

        return Maybe::just(SideEffect::identity);
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
        $_ = $cookie
            ->parameters()
            ->values()
            ->find(static fn($parameter) => $parameter->name() === $sessionName)
            ->match(
                static fn($parameter) => \session_id($parameter->value()),
                static fn() => null,
            );
    }
}
