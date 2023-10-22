<?php
declare(strict_types = 1);

namespace Innmind\HttpSession;

use Innmind\Http\ServerRequest;
use Innmind\Immutable\{
    Maybe,
    SideEffect,
};

interface Manager
{
    /**
     * @return Maybe<Session>
     */
    public function start(ServerRequest $request): Maybe;

    /**
     * @return Maybe<SideEffect>
     */
    public function save(Session $session): Maybe;

    /**
     * @return Maybe<SideEffect>
     */
    public function close(Session $session): Maybe;
}
