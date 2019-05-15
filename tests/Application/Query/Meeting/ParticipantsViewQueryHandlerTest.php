<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantsViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantsViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantsViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ObjectProphecy|TranslatorInterface */
    private $translator;

    /** @var ObjectProphecy|ParticipantsViewQueryHandler */
    private $participantsViewQueryHandler;

    public function setUp()
    {
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);

        $this->participantsViewQueryHandler = new ParticipantsViewQueryHandler(
            $this->participantInfoGuesser->reveal(),
            $this->translator->reveal()
        );
    }

    public function testHandle()
    {
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $user1 = $this->prophesize(User::class);
        $user1->getId()->shouldBeCalled()->willReturn(46);
        $user2 = $this->prophesize(User::class);
        $user2->getId()->shouldBeCalled()->willReturn(51);
        $user3 = $this->prophesize(User::class);
        $user3->getId()->shouldBeCalled()->willReturn(314);

        $contact1 = $this->prophesize(Contact::class);
        $contact2 = $this->prophesize(Contact::class);
        $contact3 = $this->prophesize(Contact::class);

        $this->translator->trans('gender.man', [], 'messages')->shouldBeCalled()->willReturn('Monsieur');
        $this->translator->trans('gender.woman', [], 'messages')->shouldBeCalled()->willReturn('Madame');

        $contact1->getContact()->shouldBeCalled()->willReturn($user1->reveal());
        $contact1->getEvaluation()->shouldBeCalled()->willReturn(3);
        $contact1->getComment()->shouldBeCalled()->willReturn('Le cubisme');
        $participant1->getUser()->shouldBeCalled()->willReturn($user1->reveal());
        $participant1->getEmail()->shouldBeCalled()->willReturn('pablo@picas.so');

        $contact2->getContact()->shouldBeCalled()->willReturn($user2->reveal());
        $contact2->getEvaluation()->shouldBeCalled()->willReturn(4);
        $contact2->getComment()->shouldBeCalled()->willReturn('');
        $participant2->getUser()->shouldBeCalled()->willReturn($user2->reveal());
        $participant2->getEmail()->shouldBeCalled()->willReturn('paloma@picas.so');

        $contact3->getContact()->shouldBeCalled()->willReturn($user3->reveal());
        $contact3->getEvaluation()->shouldBeCalled()->willReturn(5);
        $contact3->getComment()->shouldBeCalled()->willReturn('Haute Rennaissance');
        $participant3->getUser()->shouldBeCalled()->willReturn($user3->reveal());
        $participant3->getEmail()->shouldBeCalled()->willReturn('leonardo@da.vinci');

        $this->participantInfoGuesser
            ->guessParticipantInfos($participant1->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    Tag::PARTICIPANT_FIRSTNAME => 'Pablo',
                    Tag::PARTICIPANT_LASTNAME => 'Picasso',
                    Tag::PARTICIPANT_POSITION => 'painter',
                    Tag::PARTICIPANT_PHONE => '+33999',
                    Tag::PARTICIPANT_GENDER => 'man',
                    'pablo@picas.so',
                    3,
                    'Le cubisme'
                ]
            )
        ;

        $this->participantInfoGuesser
            ->guessParticipantInfos($participant2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    Tag::PARTICIPANT_FIRSTNAME => 'Paloma',
                    Tag::PARTICIPANT_LASTNAME => 'Picasso',
                    Tag::PARTICIPANT_POSITION => 'painter',
                    Tag::PARTICIPANT_PHONE => '+33997',
                    Tag::PARTICIPANT_GENDER => 'woman',
                    'paloma@picas.so',
                    4,
                    ''
                ]
            )
        ;

        $this->participantInfoGuesser
            ->guessParticipantInfos($participant3->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(
                [
                    Tag::PARTICIPANT_FIRSTNAME => 'Leonardo',
                    Tag::PARTICIPANT_LASTNAME => 'Da Vinci',
                    Tag::PARTICIPANT_POSITION => 'painter',
                    Tag::PARTICIPANT_PHONE => '+33666',
                    Tag::PARTICIPANT_GENDER => '',
                    'leonardo@da.vinci',
                    5,
                    'Haute Rennaissance'
                ]
            )
        ;



        $participantOfContactView1 = new MeetingParticipantView(
            'Pablo',
            'Picasso',
            'painter',
            '+33999',
            'Monsieur',
            'pablo@picas.so',
            3,
            'Le cubisme'
        );
        $participantOfContactView2 = new MeetingParticipantView(
            'Paloma',
            'Picasso',
            'painter',
            '+33997',
            'Madame',
            'paloma@picas.so',
            4,
            ''
        );
        $participantOfContactView3 = new MeetingParticipantView(
            'Leonardo',
            'Da Vinci',
            'painter',
            '+33666',
            '',
            'leonardo@da.vinci',
            5,
            'Haute Rennaissance'
        );

        $participantView = [$participantOfContactView1, $participantOfContactView2, $participantOfContactView3];

        $this->assertEquals(
            $participantView,
            $this->participantsViewQueryHandler->handle(
                new ParticipantsViewQuery([$participant1->reveal(), $participant2->reveal(), $participant3->reveal()], 'fr', [$contact1->reveal(), $contact2->reveal(), $contact3->reveal()])
            )
        );
    }

}
