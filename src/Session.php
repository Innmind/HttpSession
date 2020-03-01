<?php
declare(strict_types = 1);

namespace Innmind\HttpSession;

use Innmind\HttpSession\Session\{
    Id,
    Name,
};
use Innmind\Immutable\MapInterface;

final class Session
{
    private Id $id;
    private Name $name;
    private MapInterface $values;

    public function __construct(Id $id, Name $name, MapInterface $values)
    {
        if (
            (string) $values->keyType() !== 'string' ||
            (string) $values->valueType() !== 'mixed'
        ) {
            throw new \TypeError('Argument 3 must be of type MapInterface<string, mixed>');
        }

        $this->id = $id;
        $this->name = $name;
        $this->values = $values;
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
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->values->get($key);
    }

    public function has(string $key): bool
    {
        return $this->values->contains($key);
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->values = $this->values->put($key, $value);
    }

    /**
     * To be used only when persisting session to storage
     *
     * It should not be used by users
     *
     * @return MapInterface<string, mixed>
     */
    public function all(): MapInterface
    {
        return $this->values;
    }
}
