<?php
declare(strict_types = 1);

namespace Innmind\HttpSession;

use Innmind\HttpSession\Session\{
    Id,
    Name,
};
use Innmind\Immutable\Map;

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

    public function get(string $key): mixed
    {
        return $this->values->get($key)->match(
            static fn(mixed $value): mixed => $value,
            static fn() => throw new \LogicException,
        );
    }

    public function contains(string $key): bool
    {
        return $this->values->contains($key);
    }

    public function set(string $key, mixed $value): void
    {
        $this->values = $this->values->put($key, $value);
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
