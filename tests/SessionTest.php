<?php
declare(strict_types = 1);

namespace Tests\Innmind\HttpSession;

use Innmind\HttpSession\{
    Session,
    Session\Id,
    Session\Name,
};
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testInterface()
    {
        $session = new Session(
            $id = new Id('foo'),
            $name = new Name('bar'),
            $values = Map::of(['baz', 'foo']),
        );

        $this->assertSame($id, $session->id());
        $this->assertSame($name, $session->name());
        $this->assertSame($values, $session->values());
        $this->assertTrue($session->contains('baz'));
        $this->assertFalse($session->contains('foo'));
        $this->assertSame('foo', $session->get('baz'));
        $this->assertNull($session->set('foobar', 42));
        $this->assertSame(42, $session->get('foobar'));
        $this->assertSame(42, $session->values()->get('foobar')->match(
            static fn($value) => $value,
            static fn() => null,
        ));
    }
}
