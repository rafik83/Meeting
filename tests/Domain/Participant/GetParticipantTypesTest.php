<?php

namespace Proximum\Vimeet\Tests\Domain\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\GetParticipantTypes;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class GetParticipantTypesTest extends TestCase
{
    /** @var ObjectProphecy of TypeRepositoryInterface */
    private $typeRepository;

    /** @var GetParticipantTypes */
    private $getParticipantTypes;

    /** @var ObjectProphecy of Sheet */
    private $sheet;

    /** @var ObjectProphecy of Sheet */
    private $participant;

    /** @var ObjectProphecy of Type */
    private $type1;

    /** @var ObjectProphecy of Type */
    private $type2;

    /** @var ObjectProphecy of Event */
    private $event;

    /** @var ObjectProphecy of User */
    private $user;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type1 = $this->prophesize(Type::class);
        $this->type2 = $this->prophesize(Type::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getType()->willReturn($this->type1->reveal());
        $this->sheet->getEvent()->willReturn($this->event->reveal());

        $this->user = $this->prophesize(User::class);
        $this->user->getId()->willReturn(1337);

        $this->participant = $this->prophesize(Participant::class);
        $this->participant->getSheet()->willReturn($this->sheet->reveal());
        $this->participant->getUser()->willReturn($this->user->reveal());

        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $this->getParticipantTypes = new GetParticipantTypes($this->typeRepository->reveal());
    }

    public function testHandleSheetWithoutGroup()
    {
        $this->sheet->hasGroup()->shouldBeCalled()->willReturn(false);

        $this->assertEquals(
            [$this->type1->reveal()],
            $this->getParticipantTypes->handle($this->participant->reveal())
        );
    }

    public function testHandleSheetWithGroup()
    {
        $this->sheet->hasGroup()->shouldBeCalled()->willReturn(true);

        $this
            ->typeRepository
            ->getTypesByUserIds($this->event->reveal(), [1337])
            ->shouldBeCalled()
            ->willReturn([$this->type1->reveal(), $this->type2->reveal()])
        ;

        $this->assertEquals(
            [$this->type1->reveal(), $this->type2->reveal()],
            $this->getParticipantTypes->handle($this->participant->reveal())
        );
    }
}
