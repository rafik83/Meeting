<?php


namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRule;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRulesResolver;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantsViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantsViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantsViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ObjectProphecy|TranslatorInterface */
    private $translator;

    /** @var ObjectProphecy|ParticipantsViewQueryHandler */
    private $participantsViewQueryHandler;

    /** @var ObjectProphecy|ParticipantInfoAccessRulesResolver */
    private $participantInfoAccessRulesResolver;

    /** @var ObjectProphecy|ContactRepositoryInterface */
    private $contactRepository;

    public function setUp()
    {
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->participantInfoAccessRulesResolver = $this->prophesize(participantInfoAccessRulesResolver::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->contactRepository = $this->prophesize(ContactRepositoryInterface::class);

        $this->participantsViewQueryHandler = new ParticipantsViewQueryHandler(
            $this->participantInfoGuesser->reveal(),
            $this->participantInfoAccessRulesResolver->reveal(),
            $this->translator->reveal(),
            $this->contactRepository->reveal()
        );
    }

    public function testHandle()
    {
        $sheet = $this->prophesize(Sheet::class);

        $event = $this->prophesize(Event::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $me = $this->prophesize(User::class);
        $me->getId()->shouldBeCalled()->willReturn(1);
        $me->getFullname()->shouldBeCalled()->willReturn('Hervé DUPOND');

        $this->contactRepository->getEvaluationContactByEventAndUser(
            $event->reveal(),
            Argument::type(User::class),
            $me->reveal()
        )->shouldBeCalled()->willReturn(null);

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $myColleague = $this->prophesize(User::class);
        $myColleague->getId()->shouldBeCalled()->willReturn(2);
        $myColleague->getFullname()->shouldBeCalled()->willReturn('Samira BOUAKI');

        $userContact1 = $this->prophesize(User::class);
        $userContact1->getId()->shouldBeCalled()->willReturn(46);
        $userContact2 = $this->prophesize(User::class);
        $userContact2->getId()->shouldBeCalled()->willReturn(51);
        $userContact3 = $this->prophesize(User::class);
        $userContact3->getId()->shouldBeCalled()->willReturn(314);

        $contact1 = $this->prophesize(Contact::class);
        $contact2 = $this->prophesize(Contact::class);
        $contact3 = $this->prophesize(Contact::class);
        $contact4 = $this->prophesize(Contact::class);

        $this->translator->trans('event.meeting.listRequest.contact.insufficient_evaluation', [], 'messages', 'fr')->shouldBeCalled()->willReturn('Insufficient evaluation');
        $this->translator->trans('event.meeting.listRequest.contact.unavailable', [], 'messages', 'fr')->shouldBeCalled()->willReturn('Contact unavailable');
        $this->translator->trans('gender.man', [], 'messages')->shouldBeCalled()->willReturn('Monsieur');
        $this->translator->trans('gender.woman', [], 'messages')->shouldBeCalled()->willReturn('Madame');

        $contact1->getUser()->shouldBeCalled()->willReturn($me->reveal());
        $contact1->getContact()->shouldBeCalled()->willReturn($userContact1->reveal());
        $contact1->hasEvaluation()->shouldBeCalled()->willReturn(true);
        $contact1->getEvaluation()->shouldBeCalled()->willReturn(3);
        $contact1->hasComment()->shouldBeCalled()->willReturn(true);
        $contact1->getComment()->shouldBeCalled()->willReturn('To follow');
        $participant1->getUser()->shouldBeCalled()->willReturn($userContact1->reveal());
        $participant1->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $participant1->getEmail()->shouldBeCalled()->willReturn('pablo@picas.so');

        $contact2->getUser()->shouldBeCalled()->willReturn($me->reveal());
        $contact2->getContact()->shouldBeCalled()->willReturn($userContact2->reveal());
        $contact2->hasEvaluation()->shouldBeCalled()->willReturn(true);
        $contact2->getEvaluation()->shouldBeCalled()->willReturn(4);
        $contact2->hasComment()->shouldBeCalled()->willReturn(false);
        $contact2->getComment()->shouldNotBeCalled();
        $participant2->getUser()->shouldBeCalled()->willReturn($userContact2->reveal());
        $participant2->getEmail()->shouldBeCalled()->willReturn('paloma@picas.so');
        $participant2->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());

        $contact3->getContact()->shouldBeCalled()->willReturn($userContact3->reveal());
        $contact3->hasEvaluation()->shouldBeCalled()->willReturn(false);
        $contact3->getEvaluation()->shouldNotBeCalled();
        $contact3->hasComment()->shouldBeCalled()->willReturn(false);
        $contact3->getComment()->shouldNotBeCalled();
        $participant3->getUser()->shouldBeCalled()->willReturn($userContact3->reveal());
        $participant3->getEmail()->shouldBeCalled()->willReturn('leonardo@da.vinci');
        $participant3->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());

        $contact4->getUser()->shouldBeCalled()->willReturn($myColleague->reveal());
        $contact4->getContact()->shouldBeCalled()->willReturn($userContact1->reveal());
        $contact4->hasEvaluation()->shouldBeCalled()->willReturn(true);
        $contact4->getEvaluation()->shouldBeCalled()->willReturn(4);
        $contact4->hasComment()->shouldBeCalled()->willReturn(true);
        $contact4->getComment()->shouldBeCalled()->willReturn('Interesting');

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
            "Hervé DUPOND: 3\nSamira BOUAKI: 4",
            "Hervé DUPOND: To follow\nSamira BOUAKI: Interesting"
        );
        $participantOfContactView2 = new MeetingParticipantView(
            'Paloma',
            'Picasso',
            'painter',
            '+33997',
            'Madame',
            'paloma@picas.so',
            'Hervé DUPOND: 4',
            null
        );
        $participantOfContactView3 = new MeetingParticipantView(
            'Leonardo',
            'Da Vinci',
            'painter',
            '+33666',
            '',
            'leonardo@da.vinci',
            null,
            null
        );

        $participantView = [$participantOfContactView1, $participantOfContactView2, $participantOfContactView3];

        $this->participantInfoAccessRulesResolver
            ->getParticipantInfoAccessRule($sheet->reveal(), $sheet->reveal())
            ->shouldBeCalled()
            ->willReturn(new ParticipantInfoAccessRule(null, null, null, true));

        $this->assertEquals(
            $participantView,
            $this->participantsViewQueryHandler->handle(
                new ParticipantsViewQuery(
                    [$participant1->reveal(), $participant2->reveal(), $participant3->reveal()],
                    'fr',
                    [$contact1->reveal(), $contact2->reveal(), $contact3->reveal(), $contact4->reveal()],
                    $sheet->reveal(),
                    $me->reveal()
                )
            )
        );
    }

}
