<?php

namespace Proximum\Vimeet\Tests\Application\Components\Contact;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Contact\CanParticipantSeeContact;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersView;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQuery;
use Proximum\Vimeet\Application\Query\Contact\GetContactListUsersViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;

class CanParticipantSeeContactTest extends TestCase
{
    public function testTrue(): void
    {
        // set data
        $event = $this->prophesize(Event::class);

        $seerParticipant = $this->prophesize(Participant::class);
        $seerParticipant->getEvent()->willReturn($event->reveal());

        $seenUser = $this->prophesize(User::class);

        // prophecies dependencies
        $getContactListUsersViewQueryHandler = $this->prophesize(GetContactListUsersViewQueryHandler::class);
        $getContactListUsersViewQueryHandler->handle(
            new GetContactListUsersViewQuery($event->reveal(), $seerParticipant->reveal())
        )
            ->willReturn(new GetContactListUsersView([], [$seenUser->reveal()], []))
        ;

        // run tests
        $canParticipantSeeContact = new CanParticipantSeeContact($getContactListUsersViewQueryHandler->reveal());
        $result = $canParticipantSeeContact->isSatisfiedBy($seerParticipant->reveal(), $seenUser->reveal());

        $this->assertTrue($result);
    }

    public function testFalse(): void
    {
        // set data
        $event = $this->prophesize(Event::class);

        $seerParticipant = $this->prophesize(Participant::class);
        $seerParticipant->getEvent()->willReturn($event->reveal());

        $seenUser = $this->prophesize(User::class);

        // prophecies dependencies
        $getContactListUsersViewQueryHandler = $this->prophesize(GetContactListUsersViewQueryHandler::class);
        $getContactListUsersViewQueryHandler->handle(
            new GetContactListUsersViewQuery($event->reveal(), $seerParticipant->reveal())
        )
            ->willReturn(new GetContactListUsersView([], [], []))
        ;

        // run tests
        $canParticipantSeeContact = new CanParticipantSeeContact($getContactListUsersViewQueryHandler->reveal());
        $result = $canParticipantSeeContact->isSatisfiedBy($seerParticipant->reveal(), $seenUser->reveal());

        $this->assertFalse($result);
    }
}
