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
use Innmind\Http\Message\ServerRequest;
use Innmind\Url\PathInterface;
use Innmind\Immutable\Map;

final class Native implements Manager
{
    private $session;
    private $request;

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

        if (\session_start() === false) {
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
        if (!$this->has($request)) {
            throw new LogicException;
        }

        return $this->session;
    }

    public function has(ServerRequest $request): bool
    {
        return $this->request === $request;
    }

    public function save(ServerRequest $request): void
    {
        if (!$this->has($request)) {
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
        if (!$this->has($request)) {
            throw new LogicException;
        }

        if (\session_destroy() === false) {
            throw new FailedToCloseSession;
        }

        $this->session = null;
        $this->request = null;
    }
}
