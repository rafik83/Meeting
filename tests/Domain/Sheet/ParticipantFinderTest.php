<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Sheet\ParticipantFinder;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ParticipantFinderTest extends TestCase
{
    public function testHasParticipantFalse()
    {
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 2);
        $property->setAccessible(false);

        $this->assertEquals(
            false,
            ParticipantFinder::hasParticipantWithId($sheet, 1)
        );
    }

    public function testHasParticipantTrue()
    {
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 2);
        $property->setAccessible(false);

        $this->assertEquals(
            true,
            ParticipantFinder::hasParticipantWithId($sheet, 2)
        );
    }

    public function testGetParticipantNotFound()
    {
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 2);
        $property->setAccessible(false);

        $this->assertEquals(
            null,
            ParticipantFinder::getParticipantWithId($sheet, 1)
        );
    }

    public function testGetParticipant()
    {
        $sheet       = SheetFactory::create();
        $participant = ParticipantFactory::create($sheet);

        $reflection = new \ReflectionClass(Participant::class);
        $property   = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($participant, 2);
        $property->setAccessible(false);

        $this->assertEquals(
            $participant,
            ParticipantFinder::getParticipantWithId($sheet, 2)
        );
    }

    public function testGetParticipantWithUserId()
    {
        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(111);
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());

        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(222);
        $participant2 = $this->prophesize(Participant::class);
        $participant2->getUser()->shouldBeCalled()->willReturn($user2->reveal());

        // Sheet with unknown participant
        $sheetWithUnknownParticipant = $this->prophesize(Sheet::class);
        $sheetWithUnknownParticipant->hasLinkedSheets()->shouldBeCalled()->willReturn(false);
        $sheetWithUnknownParticipant
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()])
        ;
        $sheetWithUnknownParticipant
            ->getLinkedSheetsParticipants()
            ->shouldNotBeCalled()
        ;
        $this->assertNull(ParticipantFinder::getParticipantWithUserId($sheetWithUnknownParticipant->reveal(), 333));

        // Sheet with linked sheets and unknown participant
        $sheetWithLinkedSheetsAndUnknownParticipant = $this->prophesize(Sheet::class);
        $sheetWithLinkedSheetsAndUnknownParticipant->hasLinkedSheets()->shouldBeCalled()->willReturn(true);
        $sheetWithLinkedSheetsAndUnknownParticipant
            ->getParticipantsArray()
            ->shouldNotBeCalled()
        ;
        $sheetWithLinkedSheetsAndUnknownParticipant
            ->getLinkedSheetsParticipants()
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()])
        ;
        $this->assertNull(ParticipantFinder::getParticipantWithUserId($sheetWithLinkedSheetsAndUnknownParticipant->reveal(), 333));

        // Sheet without linked sheets
        $sheetWithoutLinkedSheets = $this->prophesize(Sheet::class);
        $sheetWithoutLinkedSheets->hasLinkedSheets()->shouldBeCalled()->willReturn(false);
        $sheetWithoutLinkedSheets
            ->getParticipantsArray()
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()])
        ;
        $sheetWithoutLinkedSheets
            ->getLinkedSheetsParticipants()
            ->shouldNotBeCalled()
        ;
        $this->assertEquals(
            $participant2->reveal(),
            ParticipantFinder::getParticipantWithUserId($sheetWithoutLinkedSheets->reveal(), 222)
        );

        // Sheet with linked sheets
        $sheetWithLinkedSheets = $this->prophesize(Sheet::class);
        $sheetWithLinkedSheets->hasLinkedSheets()->shouldBeCalled()->willReturn(true);
        $sheetWithLinkedSheets
            ->getParticipantsArray()
            ->shouldNotBeCalled()
        ;
        $sheetWithLinkedSheets
            ->getLinkedSheetsParticipants()
            ->shouldBeCalled()
            ->willReturn([$participant1->reveal(), $participant2->reveal()])
        ;
        $this->assertEquals(
            $participant1->reveal(),
            ParticipantFinder::getParticipantWithUserId($sheetWithLinkedSheets->reveal(), 111)
        );
    }
}
