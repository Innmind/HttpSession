<?php
declare(strict_types = 1);

namespace Innmind\HttpSession\Session;

use Innmind\Immutable\{
    Str,
    Maybe,
};

/**
 * The cookie name the id will be stored in
 * @psalm-immutable
 */
final class Name
{
    private function __construct(private string $value)
    {
    }

    /**
     * @psalm-pure
     *
     * @return Maybe<self>
     */
    #[\NoDiscard]
    public static function maybe(string $value): Maybe
    {
        return Maybe::just(Str::of($value))
            ->filter(static fn($value) => $value->matches('~^[\w\-\_]+$~'))
            ->map(static fn($value) => new self($value->toString()));
    }

    #[\NoDiscard]
    public function toString(): string
    {
        return $this->value;
    }
}
