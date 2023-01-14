<?php
declare(strict_types = 1);

namespace Innmind\HttpSession\Manager;

use Innmind\HttpSession\{
    Manager,
    Session,
    Session\Id,
    Session\Name,
    Exception\LogicException,
    Exception\ConcurrentSessionNotSupported,
    Exception\FailedToStartSession,
    Exception\FailedToSaveSession,
    Exception\FailedToCloseSession,
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
};

final class Native implements Manager
{
    private ?Session $session = null;
    private ?ServerRequest $request = null;

    public function __construct(Path $save = null)
    {
        if ($save instanceof Path) {
            $_ = \session_save_path($save->toString());
        }
    }

    public function start(ServerRequest $request): Session
    {
        if ($this->request instanceof ServerRequest) {
            throw new ConcurrentSessionNotSupported;
        }

        $this->configureSessionId($request);

        if (\session_start(['use_cookies' => false]) === false) {
            throw new FailedToStartSession;
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

        $session = Session::of(
            new Id(\session_id()),
            new Name(\session_name()),
            $values,
        );
        $this->request = $request;
        $this->session = $session;

        return $session;
    }

    /**
     * @psalm-suppress InvalidNullableReturnType Because request and session are always set together
     */
    public function get(ServerRequest $request): Session
    {
        if (!$this->contains($request)) {
            throw new LogicException('No session started');
        }

        /** @psalm-suppress NullableReturnStatement Because request and session are always set together */
        return $this->session;
    }

    public function contains(ServerRequest $request): bool
    {
        return $this->request === $request;
    }

    public function save(ServerRequest $request): void
    {
        if (!$this->contains($request)) {
            throw new LogicException('No session started');
        }

        /** @psalm-suppress PossiblyNullReference */
        $_ = $this
            ->session
            ->values()
            ->foreach(static function(string $key, $value): void {
                /** @psalm-suppress MixedAssignment */
                $_SESSION[$key] = $value;
            });

        if (\session_write_close() === false) {
            throw new FailedToSaveSession;
        }

        $this->session = null;
        $this->request = null;
        $_SESSION = [];
    }

    public function close(ServerRequest $request): void
    {
        if (!$this->contains($request)) {
            throw new LogicException('No session started');
        }

        if (\session_destroy() === false) {
            throw new FailedToCloseSession;
        }

        $this->session = null;
        $this->request = null;
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
