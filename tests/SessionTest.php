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
            $values = (new Map('string', 'mixed'))
                ->put('baz', 'foo')
        );

        $this->assertSame($id, $session->id());
        $this->assertSame($name, $session->name());
        $this->assertSame($values, $session->all());
        $this->assertTrue($session->contains('baz'));
        $this->assertFalse($session->contains('foo'));
        $this->assertSame('foo', $session->get('baz'));
        $this->assertNull($session->set('foobar', 42));
        $this->assertSame(42, $session->get('foobar'));
        $this->assertSame(42, $session->all()->get('foobar'));
    }

    public function testThrowWhenInvalidValuesKey()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type MapInterface<string, mixed>');

        new Session(
            new Id('foo'),
            new Name('bar'),
            new Map('scalar', 'mixed')
        );
    }

    public function testThrowWhenInvalidValuesValue()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type MapInterface<string, mixed>');

        new Session(
            new Id('foo'),
            new Name('bar'),
            new Map('string', 'variable')
        );
    }
}
