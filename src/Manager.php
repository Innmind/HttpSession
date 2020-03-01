<?php
declare(strict_types = 1);

namespace Innmind\HttpSession;

use Innmind\Http\Message\ServerRequest;

interface Manager
{
    public function start(ServerRequest $request): Session;
    public function get(ServerRequest $request): Session;
    public function contains(ServerRequest $request): bool;
    public function save(ServerRequest $request): void;
    public function close(ServerRequest $request): void;
}
