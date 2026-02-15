<?php
declare(strict_types = 1);

namespace Innmind\HttpSession;

use Innmind\Http\ServerRequest;
use Innmind\Immutable\{
    Attempt,
    SideEffect,
};

interface Manager
{
    /**
     * @return Attempt<Session>
     */
    #[\NoDiscard]
    public function start(ServerRequest $request): Attempt;

    /**
     * @return Attempt<SideEffect>
     */
    #[\NoDiscard]
    public function save(Session $session): Attempt;

    /**
     * @return Attempt<SideEffect>
     */
    #[\NoDiscard]
    public function close(Session $session): Attempt;
}
