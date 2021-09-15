<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\Query;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\PrepareLeaderData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\PrepareLeaderDataHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeaderView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class PrepareLeaderDataHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $extraDataRepository;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $participant;

    /** @var ObjectProphecy */
    private $participant2;

    /** @var ObjectProphecy */
    private $participant3;

    /** @var ObjectProphecy */
    private $sheet;

    public function setUp()
    {
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->participant2 = $this->prophesize(Participant::class);
        $this->participant3 = $this->prophesize(Participant::class);
        $this->user = $this->prophesize(User::class);
        $this->participant->getUser()->willReturn($this->user->reveal());
    }

    public function testHandleOwner()
    {
        $this->sheet->isOwner($this->user->reveal())->shouldBeCalled()->willReturn(true);

        $handler = new PrepareLeaderDataHandler(
            $this->extraDataRepository->reveal()
        );

        $result = $handler->handle(new PrepareLeaderData($this->sheet->reveal(), $this->user->reveal()));

        $this->assertNull($result);
    }

    public function testHandleIsFirstParticipant()
    {
        $this->participant->getId()->willReturn(1);
        $this->participant2->getId()->willReturn(9);
        $this->participant3->getId()->willReturn(4);
        $this->sheet->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn($this->participant->reveal());

        $participants = [
            $this->participant->reveal(),
            $this->participant2->reveal(),
            $this->participant3->reveal(),
        ];
        $this->sheet->isOwner($this->user->reveal())->shouldBeCalled()->willReturn(false);
        $this->sheet->getParticipantsArray()->willReturn($participants);

        $handler = new PrepareLeaderDataHandler(
            $this->extraDataRepository->reveal()
        );

        $result = $handler->handle(new PrepareLeaderData($this->sheet->reveal(), $this->user->reveal()));

        $this->assertNull($result);
    }

    public function testHandleNoLeniUserId()
    {
        $event = $this->prophesize(Event::class);
        $user2 = $this->prophesize(User::class);

        $this->participant->getId()->willReturn(12);
        $this->participant2->getId()->willReturn(9);
        $this->participant3->getId()->willReturn(4);
        $this->sheet->getEvent()->willReturn($event->reveal());
        $this->participant3->getUser()->willReturn($user2->reveal());

        $participants = [
            $this->participant->reveal(),
            $this->participant2->reveal(),
            $this->participant3->reveal(),
        ];
        $this->sheet->isOwner($this->user->reveal())->shouldBeCalled()->willReturn(false);
        $this->sheet->getParticipantsArray()->willReturn($participants);
        $this->sheet->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn($this->participant->reveal());

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                Type::LENI_USER_ID,
                $user2->reveal()
            )->shouldBeCalled()
            ->willReturn(null)
        ;

        $handler = new PrepareLeaderDataHandler(
            $this->extraDataRepository->reveal()
        );

        $result = $handler->handle(new PrepareLeaderData($this->sheet->reveal(), $this->user->reveal()));

        $this->assertNull($result);
    }

    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user2 = $this->prophesize(User::class);

        $this->participant->getId()->willReturn(12);
        $this->participant2->getId()->willReturn(9);
        $this->participant3->getId()->willReturn(4);
        $this->sheet->getEvent()->willReturn($event->reveal());

        $this->participant3->getUser()->willReturn($user2->reveal());
        $user2->getFirstName()->willReturn('firstName');
        $user2->getLastName()->willReturn('lastName');
        $this->participant3->getEmail()->willReturn('email@example.net');
        $this->sheet->getTitle()->willReturn('sheetName');

        $participants = [
            $this->participant->reveal(),
            $this->participant2->reveal(),
            $this->participant3->reveal(),
        ];
        $this->sheet->isOwner($this->user->reveal())->shouldBeCalled()->willReturn(false);
        $this->sheet->getParticipantsArray()->willReturn($participants);
        $this->sheet->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn($this->participant->reveal());

        $extraData = $this->prophesize(User\Event\ExtraData::class);
        $extraData->getValue()->willReturn('123-321');

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                Type::LENI_USER_ID,
                $user2->reveal()
            )->shouldBeCalled()
            ->willReturn($extraData)
        ;

        $handler = new PrepareLeaderDataHandler(
            $this->extraDataRepository->reveal()
        );

        $expected = new LeaderView(
            '123-321',
            'email@example.net',
            'firstName',
            'lastName',
            'sheetName'
        );
        $result = $handler->handle(new PrepareLeaderData($this->sheet->reveal(), $this->user->reveal()));

        $this->assertEquals($expected, $result);
    }
}
