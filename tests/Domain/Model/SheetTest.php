<?php

namespace Proximum\Vimeet\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Exception\Sheet\SheetException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SheetTest extends TestCase
{
    public function testHasUser()
    {
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user1 = new User('user1@test.com', '', '', 'fr');
        $sheet = new Sheet($event, $type, [], $user1, new \DateTime());

        $user2 = new User('user2@test.com', '', '', 'fr');
        $user3 = new User('user3@test.com', '', '', 'fr');

        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user1, 1);
        $property->setValue($user2, 2);
        $property->setValue($user3, 3);
        $property->setAccessible(false);

        $sheet->addParticipant(new Participant($sheet, $user1, [], true, new \DateTime()));
        $sheet->addParticipant(new Participant($sheet, $user2, [], false, new \DateTime()));

        $this->assertTrue($sheet->hasUser($user1));
        $this->assertTrue($sheet->hasUser($user2));
        $this->assertFalse($sheet->hasUser($user3));
    }

    public function testHasParticipant()
    {
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());

        $participant1 = new Participant($sheet, new User('user1@test.com', '', '', 'fr'), [], true, new \DateTime());
        $participant2 = new Participant($sheet, new User('user2@test.com', '', '', 'fr'), [], true, new \DateTime());
        $participant3 = new Participant($sheet, new User('user3@test.com', '', '', 'fr'), [], true, new \DateTime());

        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);

        $this->assertTrue($sheet->hasParticipant($participant1));
        $this->assertTrue($sheet->hasParticipant($participant2));
        $this->assertFalse($sheet->hasParticipant($participant3));
    }

    public function testGetUserParticipant()
    {
        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);

        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user1 = new User('user1@test.com', '', '', 'fr');
        $property->setValue($user1, 1);
        $sheet = new Sheet($event, $type, [], $user1, new \DateTime());

        $user2 = new User('user2@test.com', '', '', 'fr');
        $property->setValue($user2, 2);

        $user3 = new User('user3@test.com', '', '', 'fr');
        $property->setValue($user3, 3);

        $user4 = new User('user4@test.com', '', '', 'fr');

        $participant1 = new Participant($sheet, $user1, [], true, new \DateTime());
        $participant2 = new Participant($sheet, $user2, [], true, new \DateTime());
        $participant3 = new Participant($sheet, $user3, [], true, new \DateTime());

        $sheet->addParticipant($participant1);
        $sheet->addParticipant($participant2);
        $sheet->addParticipant($participant3);

        $this->assertEquals($participant1, $sheet->getUserParticipant($user1));
        $this->assertEquals($participant2, $sheet->getUserParticipant($user2));
        $this->assertEquals($participant3, $sheet->getUserParticipant($user3));
        $this->assertNull($sheet->getUserParticipant($user4));

        $property->setAccessible(false);
    }

    public function testAssignOrganizer()
    {
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());
        $dateTime = new \DateTime();

        $organizer = new Admin('test@test.com', '', '', 'fr', 'Test', 'Test', Admin::ROLE_ORGANIZER, $dateTime);

        $this->assertEquals($organizer, $sheet->assign($organizer)->getFollower());
    }

    public function testAssignOperator()
    {
        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());
        $dateTime = new \DateTime();

        $operator = new Admin('test@test.com', '', '', 'fr', 'Test', 'Test', Admin::ROLE_OPERATOR, $dateTime);

        $this->assertEquals($operator, $sheet->assign($operator)->getFollower());
    }

    public function testAssignSuperAdmin()
    {
        $this->expectException(SheetException::class);

        $event = EventFactory::createEvent();
        $type = new Type($event);
        $user = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, new \DateTime());
        $dateTime = new \DateTime();

        $operator = new Admin('test@test.com', '', '', 'fr', 'Test', 'Test', Admin::ROLE_SUPER_ADMIN, $dateTime);

        $sheet->assign($operator);
    }

    public function testHasUserInLinkedSheets()
    {
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $user1 = $this->prophesize(User::class);
        $user1->getId()->willReturn(1337);
        $user2 = $this->prophesize(User::class);
        $user2->getId()->willReturn(42);
        $sheet1 = new Sheet($event->reveal(), $type->reveal(), [], $user1->reveal(), new \DateTime());

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->hasUser($user2->reveal())->shouldBeCalled()->willReturn(true);

        $linkedSheets = $this->prophesize(Sheet\LinkedSheets::class);
        $linkedSheets->getSheets()->shouldBeCalled()->willReturn([$sheet1, $sheet2->reveal()]);
        $sheet1->setLinkedSheets($linkedSheets->reveal());

        $this->assertTrue($sheet1->hasUserInLinkedSheets($user2->reveal()));
    }

    public function testHasNotUserInLinkedSheets()
    {
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $user1 = $this->prophesize(User::class);
        $user1->getId()->willReturn(1337);
        $user2 = $this->prophesize(User::class);
        $user2->getId()->willReturn(42);
        $sheet1 = new Sheet($event->reveal(), $type->reveal(), [], $user1->reveal(), new \DateTime());

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->hasUser($user2->reveal())->shouldBeCalled()->willReturn(false);

        $linkedSheets = $this->prophesize(Sheet\LinkedSheets::class);
        $linkedSheets->getSheets()->shouldBeCalled()->willReturn([$sheet1, $sheet2->reveal()]);
        $sheet1->setLinkedSheets($linkedSheets->reveal());

        $this->assertFalse($sheet1->hasUserInLinkedSheets($user2->reveal()));
    }

    public function testHasLinkedSheet()
    {
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $user = $this->prophesize(User::class);
        $sheet1 = new Sheet($event->reveal(), $type->reveal(), [], $user->reveal(), new \DateTime());
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);

        $linkedSheets = $this->prophesize(Sheet\LinkedSheets::class);
        $linkedSheets->getSheets()->shouldBeCalled()->willReturn([$sheet1, $sheet2->reveal()]);
        $sheet1->setLinkedSheets($linkedSheets->reveal());

        $this->assertTrue($sheet1->hasLinkedSheet($sheet2->reveal()));
        $this->assertFalse($sheet1->hasLinkedSheet($sheet3->reveal()));
    }

    public function testGetLinkedSheetsParticipants()
    {
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $user = $this->prophesize(User::class);

        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->shouldBeCalled()->willReturn(1);

        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->shouldBeCalled()->willReturn(2);

        $participant3 = $this->prophesize(Participant::class);
        $participant3->getId()->shouldBeCalled()->willReturn(3);

        $sheet1 = new Sheet($event->reveal(), $type->reveal(), [], $user->reveal(), new \DateTime());
        $sheet1->addParticipant($participant1->reveal());

        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$participant2->reveal(), $participant3->reveal()])
        ;

        $linkedSheets = $this->prophesize(Sheet\LinkedSheets::class);
        $linkedSheets->getSheets()->shouldBeCalled()->willReturn([$sheet1, $sheet2->reveal()]);
        $sheet1->setLinkedSheets($linkedSheets->reveal());

        $this->assertEquals(
            [1 => $participant1->reveal(), 2 => $participant2->reveal(), 3 => $participant3->reveal()],
            $sheet1->getLinkedSheetsParticipants()
        );
    }
}
