<?php
declare(strict_types = 1);

namespace Tests\Innmind\HttpSession\Manager;

use Innmind\HttpSession\{
    Manager\Native,
    Manager,
    Session,
    Exception\LogicException,
    Exception\ConcurrentSessionNotSupported,
};
use Innmind\Http\Message\ServerRequest;
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

        $this->assertFalse($manager->has($request));

        $session = $manager->start($request);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertTrue($manager->has($request));
        $this->assertSame($session, $manager->get($request));
    }

    public function testThrowWhenTryingToStartMultipleSessions()
    {
        $this->expectException(ConcurrentSessionNotSupported::class);

        $manager = new Native;
        $request = $this->createMock(ServerRequest::class);

        $manager->start($request);
        $manager->start($request);
    }

    public function testThrowWhenTryingToGetSessionOfUnstartedRequest()
    {
        $this->expectException(LogicException::class);

        (new Native)->get($this->createMock(ServerRequest::class));
    }

    public function testThrowWhenTryingToGetSessionForDifferentRequest()
    {
        $manager = new Native;

        $manager->start($this->createMock(ServerRequest::class));

        $this->expectException(LogicException::class);

        $manager->get($this->createMock(ServerRequest::class));
    }

    public function testThrowWhenTryingToSaveSessionOfUnstartedRequest()
    {
        $this->expectException(LogicException::class);

        (new Native)->save($this->createMock(ServerRequest::class));
    }

    public function testThrowWhenTryingToSaveSessionForDifferentRequest()
    {
        $manager = new Native;

        $manager->start($this->createMock(ServerRequest::class));

        $this->expectException(LogicException::class);

        $manager->save($this->createMock(ServerRequest::class));
    }

    public function testThrowWhenTryingToCloseSessionOfUnstartedRequest()
    {
        $this->expectException(LogicException::class);

        (new Native)->close($this->createMock(ServerRequest::class));
    }

    public function testThrowWhenTryingToCloseSessionForDifferentRequest()
    {
        $manager = new Native;

        $manager->start($this->createMock(ServerRequest::class));

        $this->expectException(LogicException::class);

        $manager->close($this->createMock(ServerRequest::class));
    }

    public function testSave()
    {
        $manager = new Native;
        $request = $this->createMock(ServerRequest::class);

        $session = $manager->start($request);
        $session->set('foo', 'bar');

        $this->assertFalse(isset($_SESSION['foo']));
        $this->assertNull($manager->save($request));
        $this->assertFalse(isset($_SESSION['foo']));
        $this->assertSame(\PHP_SESSION_NONE, \session_status());

        $session2 = $manager->start($request);

        $this->assertNotSame($session, $session2);
        $this->assertTrue($session2->has('foo'));
        $this->assertSame('bar', $session2->get('foo'));
    }

    public function testClose()
    {
        $manager = new Native;
        $request = $this->createMock(ServerRequest::class);

        $session = $manager->start($request);
        $session->set('foo', 'bar');
        $manager->save($request);

        $session2 = $manager->start($request);
        $this->assertTrue($session2->has('foo'));
        $manager->close($request);

        $session3 = $manager->start($request);

        $this->assertNotSame($session, $session3);
        $this->assertFalse($session3->has('foo'));
        $this->assertFalse(isset($_SESSION['foo']));
    }
}
