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
    Attempt,
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
    public function start(ServerRequest $request): Attempt
    {
        if ($this->session instanceof Id) {
            /** @var Attempt<Session> */
            return Attempt::error(new \LogicException('Session already started'));
        }

        $this->configureSessionId($request);

        if (\session_start(['use_cookies' => false]) === false) {
            /** @var Attempt<Session> */
            return Attempt::error(new \RuntimeException('Failed to start session'));
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
            })
            ->attempt(static fn() => new \RuntimeException('Session id or name is invalid'));
    }

    #[\Override]
    public function save(Session $session): Attempt
    {
        if ($this->session !== $session->id()) {
            /** @var Attempt<SideEffect> */
            return Attempt::error(new \LogicException('Trying to save a different session than the started one'));
        }

        $_ = $session
            ->values()
            ->foreach(static function(string $key, $value): void {
                /** @psalm-suppress MixedAssignment */
                $_SESSION[$key] = $value;
            });

        if (\session_write_close() === false) {
            /** @var Attempt<SideEffect> */
            return Attempt::error(new \RuntimeException('Failed to persist the session data'));
        }

        $this->session = null;
        $_SESSION = [];

        return Attempt::result(SideEffect::identity);
    }

    #[\Override]
    public function close(Session $session): Attempt
    {
        if ($this->session !== $session->id()) {
            /** @var Attempt<SideEffect> */
            return Attempt::error(new \LogicException('Trying to close a different session than the started one'));
        }

        if (\session_destroy() === false) {
            /** @var Attempt<SideEffect> */
            return Attempt::error(new \RuntimeException('Failed to close the session'));
        }

        $this->session = null;

        return Attempt::result(SideEffect::identity);
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
