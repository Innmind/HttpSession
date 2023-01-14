<?php
declare(strict_types = 1);

namespace Innmind\HttpSession\Session;

use Innmind\HttpSession\Exception\DomainException;
use Innmind\Immutable\Str;

/**
 * The cookie name the id will be stored in
 * @psalm-immutable
 */
final class Name
{
    private string $value;

    public function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^[\w\-\_]+$~')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
