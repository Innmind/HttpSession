<?php
declare(strict_types = 1);

namespace Innmind\HttpSession\Session;

use Innmind\HttpSession\Exception\DomainException;
use Innmind\Immutable\Str;

final class Id
{
    private string $value;

    public function __construct(string $value)
    {
        if (!Str::of($value)->matches('~^[\w\-\_]+$~')) {
            throw new DomainException($value);
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
