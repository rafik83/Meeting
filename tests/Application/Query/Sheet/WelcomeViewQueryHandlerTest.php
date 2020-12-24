<?php

namespace Proximum\Vimeet\Tests\Application\Query\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Query\Sheet\WelcomeViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\WelcomeViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\WelcomeView;
use Proximum\Vimeet\Domain\KeyDates\Checker\HappeningsAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;

class WelcomeViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $happeningsAccessChecker;

    /** @var ObjectProphecy */
    private $session;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->happeningsAccessChecker = $this->prophesize(HappeningsAccessChecker::class);
        $this->session = $this->prophesize(SessionInterface::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());
    }

    public function testDoesNotHaveFirstRegistration()
    {
        $this->session->getFromFlashBag('first_registration')->shouldBeCalled()->willReturn([]);

        $welcomeViewQueryHandler = new WelcomeViewQueryHandler(
            $this->happeningsAccessChecker->reveal(),
            $this->session->reveal()
        );

        $this->assertNull($welcomeViewQueryHandler->handle(new WelcomeViewQuery($this->sheet->reveal())));
    }

    public function testWelcomeIsNotEnabled()
    {
        $this->session->getFromFlashBag('first_registration')->shouldBeCalled()->willReturn([true]);

        $this->event->isWelcomeEnabled()->shouldBeCalled()->willReturn(false);

        $welcomeViewQueryHandler = new WelcomeViewQueryHandler(
            $this->happeningsAccessChecker->reveal(),
            $this->session->reveal()
        );

        $this->assertNull($welcomeViewQueryHandler->handle(new WelcomeViewQuery($this->sheet->reveal())));
    }

    public function testWelcomeIsEnabledWithoutPackage()
    {
        $this->session->getFromFlashBag('first_registration')->shouldBeCalled()->willReturn([true]);

        $this->event->isWelcomeEnabled()->shouldBeCalled()->willReturn(true);

        $welcomeViewQueryHandler = new WelcomeViewQueryHandler(
            $this->happeningsAccessChecker->reveal(),
            $this->session->reveal()
        );

        $this->sheet->getPackage()->shouldBeCalled()->willReturn(null);
        $this->happeningsAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(true);

        $this->assertEquals(
            new WelcomeView(false, true),
            $welcomeViewQueryHandler->handle(new WelcomeViewQuery($this->sheet->reveal()))
        );
    }

    public function testWelcomeIsEnabledWithPassablePackage()
    {
        $this->session->getFromFlashBag('first_registration')->shouldBeCalled()->willReturn([true]);

        $this->event->isWelcomeEnabled()->shouldBeCalled()->willReturn(true);

        $welcomeViewQueryHandler = new WelcomeViewQueryHandler(
            $this->happeningsAccessChecker->reveal(),
            $this->session->reveal()
        );

        $package = $this->prophesize(Package::class);
        $this->sheet->getPackage()->shouldBeCalled()->willReturn($package->reveal());
        $package->isPassable()->shouldBeCalled()->willReturn(true);

        $this->happeningsAccessChecker->allowedToAccess($this->event->reveal())->shouldBeCalled()->willReturn(false);

        $this->assertEquals(
            new WelcomeView(true, false),
            $welcomeViewQueryHandler->handle(new WelcomeViewQuery($this->sheet->reveal()))
        );
    }
}
