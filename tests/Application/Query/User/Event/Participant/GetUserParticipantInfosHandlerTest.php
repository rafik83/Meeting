<?php

namespace Proximum\Vimeet\Tests\Application\Query\User\Event\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfos;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfosHandler;
use Proximum\Vimeet\Application\Query\User\Event\Participant\ParticipantView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class GetUserParticipantInfosHandlerTest extends TestCase
{
    /** @var GetUserParticipantInfosHandler */
    private $getUserParticipantInfosHandler;

    /** @var ObjectProphecy|ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ObjectProphecy|SheetGuesser */
    private $sheetGuesser;

    public function setUp()
    {
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->sheetGuesser = $this->prophesize(SheetGuesser::class);
        $this->getUserParticipantInfosHandler = new GetUserParticipantInfosHandler(
            $this->participantInfoGuesser->reveal(),
            $this->sheetGuesser->reveal()
        );
    }

    public function test_get_user_participant_infos()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant->reveal());

        $this->sheetGuesser
            ->getUserSheet($user->reveal(), $event->reveal(), 'en')
            ->shouldBeCalled()
            ->willReturn($sheet->reveal());

        $this->participantInfoGuesser
            ->guessParticipantInfos($participant->reveal(), 'en')
            ->shouldBeCalled()
            ->willReturn(                [
                Tag::PARTICIPANT_FIRSTNAME => 'Amélie',
                Tag::PARTICIPANT_LASTNAME => 'Poulain',
                Tag::PARTICIPANT_POSITION => 'Administrator',
                Tag::PARTICIPANT_AVATAR => '/avatar.jpg',
                Tag::PARTICIPANT_PHONE => '+3312345678',
            ]);

        $this->assertEquals(
            new ParticipantView($participant->reveal(), 'Amélie', 'Poulain', 'Administrator', '/avatar.jpg'),
            $this->getUserParticipantInfosHandler->handle(
                new GetUserParticipantInfos($event->reveal(), $user->reveal(), 'en')
            )
        );
    }
}
