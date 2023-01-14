<?php
declare(strict_types = 1);

namespace Tests\Innmind\HttpSession\Manager;

use Innmind\HttpSession\{
    Manager\Native,
    Manager,
    Session,
};
use Innmind\Http\{
    Message\ServerRequest,
    Headers,
    Header\Cookie,
    Header\CookieValue,
    Header\Parameter\Parameter,
};
use Innmind\Immutable\{
    Map,
    SideEffect,
};
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class NativeTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Manager::class, new Native);
    }

    public function testStart()
    {
        $manager = new Native;
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of());

        $session = $manager->start($request)->match(
            static fn($session) => $session,
            static fn() => null,
        );

        $this->assertInstanceOf(Session::class, $session);
    }

    public function testConfigureSessionIdFromCookieOnStart()
    {
        $manager = new Native;
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of(
                new Cookie(
                    new CookieValue(
                        new Parameter('foo', 'bar'),
                        new Parameter('PHPSESSID', 'some unique id'),
                        new Parameter('bar', 'baz'),
                    ),
                ),
            ));

        $session = $manager->start($request)->match(
            static fn($session) => $session,
            static fn() => null,
        );

        $this->assertInstanceOf(Session::class, $session);
    }

    public function testReturnNothingWhenTryingToStartMultipleSessions()
    {
        $manager = new Native;
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of());

        $this->assertInstanceOf(Session::class, $manager->start($request)->match(
            static fn($session) => $session,
            static fn() => null,
        ));
        $this->assertNull($manager->start($request)->match(
            static fn($session) => $session,
            static fn() => null,
        ));
    }

    public function testReturnNothingWhenTryingToSaveUnknownSession()
    {
        $this->assertNull(
            (new Native)
                ->save(Session::of(
                    new Session\Id('unknown'),
                    new Session\Name('foo'),
                    Map::of(),
                ))
                ->match(
                    static fn($sideEffect) => $sideEffect,
                    static fn() => null,
                ),
        );
    }

    public function testReturnNothingWhenTryingToCloseUnknownSession()
    {
        $this->assertNull(
            (new Native)
                ->close(Session::of(
                    new Session\Id('unknown'),
                    new Session\Name('foo'),
                    Map::of(),
                ))->match(
                    static fn($sideEffect) => $sideEffect,
                    static fn() => null,
                ),
        );
    }

    public function testSave()
    {
        $manager = new Native;
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of());

        $session = $manager->start($request)->match(
            static fn($session) => $session->with('foo', 'bar'),
            static fn() => null,
        );

        $this->assertFalse(isset($_SESSION['foo']));
        $this->assertInstanceOf(
            SideEffect::class,
            $manager->save($session)->match(
                static fn($sideEffect) => $sideEffect,
                static fn() => null,
            ),
        );
        $this->assertFalse(isset($_SESSION['foo']));
        $this->assertSame(\PHP_SESSION_NONE, \session_status());

        $session2 = $manager->start($request)->match(
            static fn($session) => $session,
            static fn() => null,
        );

        $this->assertNotSame($session, $session2);
        $this->assertTrue($session2->contains('foo'));
        $this->assertSame('bar', $session2->get('foo'));
    }

    public function testClose()
    {
        $manager = new Native;
        $request = $this->createMock(ServerRequest::class);
        $request
            ->expects($this->any())
            ->method('headers')
            ->willReturn(Headers::of());

        $session = $manager->start($request)->match(
            static fn($session) => $session->with('foo', 'bar'),
            static fn() => null,
        );
        $manager->save($session);

        $session2 = $manager->start($request)->match(
            static fn($session) => $session,
            static fn() => null,
        );
        $this->assertTrue($session2->contains('foo'));
        $this->assertInstanceOf(
            SideEffect::class,
            $manager->close($session2)->match(
                static fn($sideEffect) => $sideEffect,
                static fn() => null,
            ),
        );

        $session3 = $manager->start($request)->match(
            static fn($session) => $session,
            static fn() => null,
        );

        $this->assertNotSame($session, $session3);
        $this->assertNotSame($session2, $session3);
        $this->assertFalse($session3->contains('foo'));
        $this->assertFalse(isset($_SESSION['foo']));
    }
}
