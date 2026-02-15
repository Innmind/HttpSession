<?php
declare(strict_types = 1);

namespace Innmind\HttpSession\Session;

use Innmind\Immutable\{
    Str,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Id
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
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
