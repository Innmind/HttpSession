<?php
declare(strict_types = 1);

namespace Tests\Innmind\HttpSession\Session;

use Innmind\HttpSession\{
    Session\Id,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;

class IdTest extends TestCase
{
    public function testInterface()
    {
        $id = new Id('b5vcgpppij52r1krp1tbs26i27');

        $this->assertSame('b5vcgpppij52r1krp1tbs26i27', (string) $id);
    }

    public function testThrowWhenInvalidFormat()
    {
        $this->expectException(DomainException::class);

        new Id('foo.bar');
    }

    public function testThrowWhenEmptyId()
    {
        $this->expectException(DomainException::class);

        new Id('');
    }
}
