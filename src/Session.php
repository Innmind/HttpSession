<?php
declare(strict_types = 1);

namespace Innmind\HttpSession;

use Innmind\HttpSession\Session\{
    Id,
    Name,
};
use Innmind\Immutable\Map;
use function Innmind\Immutable\assertMap;

final class Session
{
    private Id $id;
    private Name $name;
    /** @var Map<string, mixed> */
    private Map $values;

    /**
     * @param Map<string, mixed> $values
     */
    public function __construct(Id $id, Name $name, Map $values)
    {
        assertMap('string', 'mixed', $values, 3);

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

    public function contains(string $key): bool
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
     * @return Map<string, mixed>
     */
    public function values(): Map
    {
        return $this->values;
    }
}
