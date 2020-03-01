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
use Innmind\Url\PathInterface;
use Innmind\Immutable\Map;

final class Native implements Manager
{
    private ?Session $session = null;
    private ?ServerRequest $request = null;

    public function __construct(PathInterface $save = null)
    {
        if ($save instanceof PathInterface) {
            \session_save_path((string) $save);
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

        $session = new Session(
            new Id(\session_id()),
            new Name(\session_name()),
            Map::of(
                'string',
                'mixed',
                array_keys($_SESSION),
                array_values($_SESSION)
            )
        );
        $this->request = $request;
        $this->session = $session;

        return $session;
    }

    public function get(ServerRequest $request): Session
    {
        if (!$this->contains($request)) {
            throw new LogicException;
        }

        return $this->session;
    }

    public function contains(ServerRequest $request): bool
    {
        return $this->request === $request;
    }

    public function save(ServerRequest $request): void
    {
        if (!$this->contains($request)) {
            throw new LogicException;
        }

        $this
            ->session
            ->all()
            ->foreach(static function(string $key, $value): void {
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
            throw new LogicException;
        }

        if (\session_destroy() === false) {
            throw new FailedToCloseSession;
        }

        $this->session = null;
        $this->request = null;
    }

    private function configureSessionId(ServerRequest $request): void
    {
        if (!$request->headers()->has('Cookie')) {
            return;
        }

        $cookie = $request->headers()->get('Cookie');

        if (!$cookie instanceof Cookie) {
            return;
        }

        $sessionName = \session_name();
        $parameters = $request
            ->headers()
            ->get('Cookie')
            ->values()
            ->current()
            ->parameters()
            ->filter(static function(string $name) use ($sessionName): bool {
                return $name === $sessionName;
            });

        if ($parameters->size() !== 1) {
            return;
        }

        \session_id($parameters->current()->value());
    }
}
