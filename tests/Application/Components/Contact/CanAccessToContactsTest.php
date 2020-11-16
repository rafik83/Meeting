<?php


namespace Proximum\Vimeet\Tests\Application\Components\Contact;


use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Contact\CanAccessToContacts;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\CanScanParticipant;

class CanAccessToContactsTest extends TestCase
{
    public function testEventNotOpen(): void {

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $canScanParticipant = $this->prophesize(CanScanParticipant::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);

        $eventOpenAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(false);


        $canAccessToContacts = new CanAccessToContacts(
            $eventOpenAccessChecker->reveal(),
            $canScanParticipant->reveal(),
            $chatSessionRepository->reveal()
        );

        $result = $canAccessToContacts->isSatisfiedBy($event->reveal(), $user->reveal(), $sheet->reveal());

        self::assertFalse($result);
    }

    public function testIsInInternalCatalog(): void {

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $canScanParticipant = $this->prophesize(CanScanParticipant::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);

        $eventOpenAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $sheet->isInInternalCatalog()->shouldBeCalled()->willReturn(true);

        $canAccessToContacts = new CanAccessToContacts(
            $eventOpenAccessChecker->reveal(),
            $canScanParticipant->reveal(),
            $chatSessionRepository->reveal()
        );

        $result = $canAccessToContacts->isSatisfiedBy($event->reveal(), $user->reveal(), $sheet->reveal());

        self::assertTrue($result);
    }

    public function testHasAStartedVisio(): void {

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $canScanParticipant = $this->prophesize(CanScanParticipant::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);

        $eventOpenAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $sheet->isInInternalCatalog()->shouldBeCalled()->willReturn(false);
        $chatSessionRepository->hasAStartedVisio($event->reveal(), $user->reveal())->shouldBeCalled()->willReturn(true);

        $canAccessToContacts = new CanAccessToContacts(
            $eventOpenAccessChecker->reveal(),
            $canScanParticipant->reveal(),
            $chatSessionRepository->reveal()
        );

        $result = $canAccessToContacts->isSatisfiedBy($event->reveal(), $user->reveal(), $sheet->reveal());

        self::assertTrue($result);
    }

    public function  testCanScanParticipant(): void {

        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);

        $eventOpenAccessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $canScanParticipant = $this->prophesize(CanScanParticipant::class);
        $chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);

        $eventOpenAccessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);
        $sheet->isInInternalCatalog()->shouldBeCalled()->willReturn(false);
        $chatSessionRepository->hasAStartedVisio($event->reveal(), $user->reveal())->shouldBeCalled()->willReturn(false);
        $canScanParticipant->isSatisfiedBy($sheet->reveal());

        $canAccessToContacts = new CanAccessToContacts(
            $eventOpenAccessChecker->reveal(),
            $canScanParticipant->reveal(),
            $chatSessionRepository->reveal()
        );

        $result = $canAccessToContacts->isSatisfiedBy($event->reveal(), $user->reveal(), $sheet->reveal());

        self::assertTrue($result);
    }


}
