<?php
declare(strict_types = 1);

namespace Innmind\HttpSession;

use Innmind\HttpSession\{
    Session\Id,
    Session\Name,
    Exception\LogicException,
};
use Innmind\Immutable\{
    Map,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Session
{
    private Id $id;
    private Name $name;
    /** @var Map<string, mixed> */
    private Map $values;

    /**
     * @param Map<string, mixed> $values
     */
    private function __construct(Id $id, Name $name, Map $values)
    {
        $this->id = $id;
        $this->name = $name;
        $this->values = $values;
    }

    /**
     * @psalm-pure
     *
     * @param Map<string, mixed> $values
     */
    public static function of(Id $id, Name $name, Map $values): self
    {
        return new self($id, $name, $values);
    }

    public function id(): Id
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @param literal-string $key
     *
     * @throws LogicException
     */
    public function get(string $key): mixed
    {
        return $this->maybe($key)->match(
            static fn(mixed $value): mixed => $value,
            static fn() => throw new LogicException,
        );
    }

    /**
     * @return Maybe<mixed>
     */
    public function maybe(string $key): Maybe
    {
        return $this->values->get($key);
    }

    public function contains(string $key): bool
    {
        return $this->values->contains($key);
    }

    public function with(string $key, mixed $value): self
    {
        return new self(
            $this->id,
            $this->name,
            ($this->values)($key, $value),
        );
    }

    /**
     * To be used only when persisting session to storage
     *
     * It should not be used by users
     *
     * @return Map<string, mixed>
     */
    public function values(): Map
    {
        return $this->values;
    }
}
