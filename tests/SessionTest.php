<?php
declare(strict_types = 1);

namespace Tests\Innmind\HttpSession;

use Innmind\HttpSession\{
    Session,
    Session\Id,
    Session\Name,
};
use Innmind\Immutable\Map;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testInterface()
    {
        $session = Session::of(
            $id = Id::maybe('foo')->match(
                static fn($id) => $id,
                static fn() => null,
            ),
            $name = Name::maybe('bar')->match(
                static fn($name) => $name,
                static fn() => null,
            ),
            $values = Map::of(['baz', 'foo']),
        );

        $this->assertSame($id, $session->id());
        $this->assertSame($name, $session->name());
        $this->assertSame($values, $session->values());
        $this->assertTrue($session->contains('baz'));
        $this->assertFalse($session->contains('foo'));
        $this->assertSame('foo', $session->get('baz'));
        $session2 = $session->with('foobar', 42);
        $this->assertNotSame($session, $session2);
        $this->assertNull($session->maybe('foobar')->match(
            static fn($value) => $value,
            static fn() => null,
        ));
        $this->assertSame(42, $session2->get('foobar'));
        $this->assertSame(42, $session2->values()->get('foobar')->match(
            static fn($value) => $value,
            static fn() => null,
        ));
        $this->assertSame(42, $session2->maybe('foobar')->match(
            static fn($value) => $value,
            static fn() => null,
        ));
        $this->assertNull($session2->maybe('foo')->match(
            static fn() => true,
            static fn() => null,
        ));
    }
}
