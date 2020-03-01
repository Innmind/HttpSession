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
};
use Innmind\Url\Path;
use Innmind\Immutable\Map;
use function Innmind\Immutable\first;

final class Native implements Manager
{
    private ?Session $session = null;
    private ?ServerRequest $request = null;

    public function __construct(Path $save = null)
    {
        if ($save instanceof Path) {
            \session_save_path($save->toString());
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
        $values = Map::of('string', 'mixed');

        /**
         * @var string $key
         * @var mixed $value
         */
        foreach ($_SESSION as $key => $value) {
            $values = ($values)($key, $value);
        }

        $session = new Session(
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
        $this
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
        if (!$request->headers()->contains('Cookie')) {
            return;
        }

        $cookie = $request->headers()->get('Cookie');

        if (!$cookie instanceof Cookie) {
            return;
        }

        $sessionName = \session_name();
        $parameters = first($cookie->values())
            ->parameters()
            ->filter(static function(string $name) use ($sessionName): bool {
                return $name === $sessionName;
            })
            ->values();

        if ($parameters->size() !== 1) {
            return;
        }

        \session_id($parameters->first()->value());
    }
}
