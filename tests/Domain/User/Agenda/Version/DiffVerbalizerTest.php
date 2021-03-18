<?php

namespace Proximum\Vimeet\Tests\Domain\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;

class DiffVerbalizerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $diffChecker;

    /** @var ObjectProphecy */
    private $translator;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $meetingSlotRepository;

    /** @var ObjectProphecy */
    private $spotRepository;

    /** @var ObjectProphecy|MessageRepositoryInterface */
    private $messageRepository;

    public function setUp()
    {
        $this->diffChecker = $this->prophesize(DiffChecker::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $this->spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $this->messageRepository = $this->prophesize(MessageRepositoryInterface::class);
        $this->messageRepository->getUpdateOrDeleteReasonMessageFromRequestIds(Argument::any())->willReturn([]);
    }

    public function testVerbalizeDiffNoDiff()
    {
        $lastVersion = $this->prophesize(Version::class);
        $lastVersion->getVersion()->willReturn([]);
        $locale = 'fr';
        $currentVersion = [];

        $diffVerbalizer = new DiffVerbalizer(
            $this->diffChecker->reveal(),
            $this->translator->reveal(),
            $this->sheetRepository->reveal(),
            $this->meetingSlotRepository->reveal(),
            $this->spotRepository->reveal(),
            $this->messageRepository->reveal()
        );
        $result = $diffVerbalizer->verbalizeDiff($lastVersion->reveal(), $currentVersion, $locale);

        $this->assertEquals('', $result);
    }

    public function testVerbalizeDiffDeletion()
    {
        $userSheet = $this->prophesize(Sheet::class);
        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet2 = $this->prophesize(Sheet::class);
        $lastVersion = $this->prophesize(Version::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $locale = 'fr';
        $currentVersion = [];
        $lastVersion->getVersion()->willReturn(
            [
                1 => [
                    'request' => 1,
                    'fromSheet' => 2,
                    'toSheet' => 3,
                ],
                2 => [
                    'request' => 2,
                    'fromSheet' => 2,
                    'toSheet' => 4,
                ],
            ]
        )
        ;
        $lastVersion->getEvent()->willReturn($event->reveal());
        $lastVersion->getUser()->willReturn($user->reveal());
        $event->getTimeZone()->willReturn('Europe/Paris');
        $sheetMet1->getTitle()->willReturn('sheet title given 1');
        $sheetMet2->getTitle()->willReturn('sheet title given 2');

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([2 => $userSheet->reveal()])
        ;
        $this->sheetRepository
            ->findByIds([3 => 3, 4 => 4])
            ->shouldBeCalled()
            ->willReturn([3 => $sheetMet1->reveal(), 4 => $sheetMet2->reveal()])
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_DELETED,
                ['%sheetTitle%' => 'sheet title given 1'],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Meeting deleted with sheet title given 1')
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_DELETED,
                ['%sheetTitle%' => 'sheet title given 2'],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Meeting deleted with sheet title given 2')
        ;

        $this->meetingSlotRepository->findByIds([])->shouldNotBeCalled();
        $this->spotRepository->getSpotsByIds([])->shouldNotBeCalled();
        $this->diffChecker->checkTwoVersion(Argument::any())->shouldNotBeCalled();

        $diffVerbalizer = new DiffVerbalizer(
            $this->diffChecker->reveal(),
            $this->translator->reveal(),
            $this->sheetRepository->reveal(),
            $this->meetingSlotRepository->reveal(),
            $this->spotRepository->reveal(),
            $this->messageRepository->reveal()
        );
        $result = $diffVerbalizer->verbalizeDiff($lastVersion->reveal(), $currentVersion, $locale);

        $this->assertEquals(
            "Meeting deleted with sheet title given 1\nMeeting deleted with sheet title given 2",
            $result
        );
    }

    public function testVerbalizeDiffAddition()
    {
        $userSheet = $this->prophesize(Sheet::class);
        $sheetMet1 = $this->prophesize(Sheet::class);
        $spot = $this->prophesize(Spot::class);
        $slot = $this->prophesize(MeetingSlot::class);
        $lastVersion = $this->prophesize(Version::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $locale = 'fr';
        $currentVersion = [
            1 => [
                'request' => 1,
                'fromSheet' => 2,
                'toSheet' => 3,
                'slot' => 9,
                'spot' => 8,
            ],
            2 => [
                'request' => 2,
                'fromSheet' => 2,
                'toSheet' => 4,
                'slot' => 12,
                'spot' => 14,
            ],
        ];
        $lastVersion->getVersion()->willReturn(
            [
                1 => [
                    'request' => 1,
                    'fromSheet' => 2,
                    'toSheet' => 3,
                    'slot' => 9,
                    'spot' => 8,
                ],
            ]
        )
        ;
        $lastVersion->getEvent()->willReturn($event->reveal());
        $lastVersion->getUser()->willReturn($user->reveal());
        $event->getTimeZone()->willReturn('Europe/Paris');
        $sheetMet1->getTitle()->willReturn('sheet title given 1');
        $spot->getReference()->willReturn('Spot Ref');
        $slot->getBegin()->willReturn(new \DateTime('2017-10-10 10:00:00.000', new \DateTimeZone('UTC')));

        // Mock
        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([2 => $userSheet->reveal()])
        ;
        $this->sheetRepository
            ->findByIds([4 => 4])
            ->shouldBeCalled()
            ->willReturn([4 => $sheetMet1->reveal()])
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_ADDED,
                [
                    '%sheetTitle%' => 'sheet title given 1',
                    '%day%' => '10/10/2017',
                    '%hour%' => '12:00',
                    '%spotRef%' => 'Spot Ref',
                ],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Meeting added with sheet title given 1 the 10/10/2017 at 12:00 in Spot Ref')
        ;

        $this->meetingSlotRepository->findByIds([12 => 12])->shouldBeCalled()->willReturn([12 => $slot->reveal()]);
        $this->spotRepository->getSpotsByIds([14 => 14])->shouldBeCalled()->willReturn([14 => $spot->reveal()]);
        $this->diffChecker
            ->checkTwoVersion(
                [1 => ['request' => 1, 'fromSheet' => 2, 'toSheet' => 3, 'slot' => 9, 'spot' => 8], 2 => ['request' => 2, 'fromSheet' => 2, 'toSheet' => 4, 'slot' => 12, 'spot' => 14]],
                1,
                ['request' => 1, 'fromSheet' => 2, 'toSheet' => 3, 'slot' => 9, 'spot' => 8]
            )->shouldBeCalled()
            ->willReturn(false)
        ;

        $diffVerbalizer = new DiffVerbalizer(
            $this->diffChecker->reveal(),
            $this->translator->reveal(),
            $this->sheetRepository->reveal(),
            $this->meetingSlotRepository->reveal(),
            $this->spotRepository->reveal(),
            $this->messageRepository->reveal()
        );
        $result = $diffVerbalizer->verbalizeDiff($lastVersion->reveal(), $currentVersion, $locale);

        $this->assertEquals(
            'Meeting added with sheet title given 1 the 10/10/2017 at 12:00 in Spot Ref',
            $result
        );
    }

    public function testVerbalizeDiffMoved()
    {
        $userSheet = $this->prophesize(Sheet::class);
        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet2 = $this->prophesize(Sheet::class);
        $spot1 = $this->prophesize(Spot::class);
        $spot2 = $this->prophesize(Spot::class);
        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);
        $lastVersion = $this->prophesize(Version::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $locale = 'fr';
        $currentVersion = [
            1 => [
                'request' => 1,
                'fromSheet' => 2,
                'toSheet' => 3,
                'slot' => 9,
                'spot' => 8,
            ],
            2 => [
                'request' => 2,
                'fromSheet' => 2,
                'toSheet' => 4,
                'slot' => 12,
                'spot' => 14,
            ],
        ];
        $lastVersion->getVersion()->willReturn(
            [
                1 => [
                    'request' => 1,
                    'fromSheet' => 2,
                    'toSheet' => 3,
                    'slot' => 10,
                    'spot' => 8,
                ],
                2 => [
                    'request' => 2,
                    'fromSheet' => 2,
                    'toSheet' => 4,
                    'slot' => 12,
                    'spot' => 16,
                ],
            ]
        )
        ;
        $lastVersion->getEvent()->willReturn($event->reveal());
        $lastVersion->getUser()->willReturn($user->reveal());
        $event->getTimeZone()->willReturn('Europe/Paris');
        $sheetMet1->getTitle()->willReturn('sheet title given 1');
        $sheetMet2->getTitle()->willReturn('sheet title given 2');
        $spot1->getReference()->willReturn('Spot Ref 1');
        $spot2->getReference()->willReturn('Spot Ref 2');
        $slot1->getBegin()->willReturn(new \DateTime('2017-10-10 10:00:00.000', new \DateTimeZone('UTC')));
        $slot2->getBegin()->willReturn(new \DateTime('2017-10-10 14:30:00.000', new \DateTimeZone('UTC')));

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([2 => $userSheet->reveal()])
        ;
        $this->sheetRepository
            ->findByIds([3 => 3, 4 => 4])
            ->shouldBeCalled()
            ->willReturn([3 => $sheetMet1->reveal(), 4 => $sheetMet2->reveal()])
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_MOVED,
                [
                    '%sheetTitle%' => 'sheet title given 1',
                    '%day%' => '10/10/2017',
                    '%hour%' => '12:00',
                    '%spotRef%' => 'Spot Ref 1',
                ],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Meeting moved with sheet title given 1 the 10/10/2017 at 12:00 in Spot Ref 1')
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_MOVED,
                [
                    '%sheetTitle%' => 'sheet title given 2',
                    '%day%' => '10/10/2017',
                    '%hour%' => '16:30',
                    '%spotRef%' => 'Spot Ref 2',
                ],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Meeting moved with sheet title given 2 the 10/10/2017 at 16:30 in Spot Ref 2')
        ;

        $this->meetingSlotRepository
            ->findByIds([9 => 9, 12 => 12])
            ->shouldBeCalled()
            ->willReturn([9 => $slot1->reveal(), 12 => $slot2->reveal()])
        ;
        $this->spotRepository
            ->getSpotsByIds([8 => 8, 14 => 14])
            ->shouldBeCalled()
            ->willReturn([8 => $spot1->reveal(), 14 => $spot2->reveal()])
        ;

        $this->diffChecker
            ->checkTwoVersion(
                [1 => ['request' => 1, 'fromSheet' => 2, 'toSheet' => 3, 'slot' => 9, 'spot' => 8], 2 => ['request' => 2, 'fromSheet' => 2, 'toSheet' => 4, 'slot' => 12, 'spot' => 14]],
                1,
                ['request' => 1, 'fromSheet' => 2, 'toSheet' => 3, 'slot' => 10, 'spot' => 8]
            )->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->diffChecker
            ->checkTwoVersion(
                [1 => ['request' => 1, 'fromSheet' => 2, 'toSheet' => 3, 'slot' => 9, 'spot' => 8], 2 => ['request' => 2, 'fromSheet' => 2, 'toSheet' => 4, 'slot' => 12, 'spot' => 14]],
                2,
                ['request' => 2, 'fromSheet' => 2, 'toSheet' => 4, 'slot' => 12, 'spot' => 16]
            )->shouldBeCalled()
            ->willReturn(true)
        ;

        $diffVerbalizer = new DiffVerbalizer(
            $this->diffChecker->reveal(),
            $this->translator->reveal(),
            $this->sheetRepository->reveal(),
            $this->meetingSlotRepository->reveal(),
            $this->spotRepository->reveal(),
            $this->messageRepository->reveal()
        );
        $result = $diffVerbalizer->verbalizeDiff($lastVersion->reveal(), $currentVersion, $locale);

        $this->assertEquals(
            "Meeting moved with sheet title given 1 the 10/10/2017 at 12:00 in Spot Ref 1\nMeeting moved with sheet title given 2 the 10/10/2017 at 16:30 in Spot Ref 2",
            $result
        );
    }

    public function testVerbalizeDiff()
    {
        $userSheet = $this->prophesize(Sheet::class);
        $sheetMet1 = $this->prophesize(Sheet::class);
        $sheetMet2 = $this->prophesize(Sheet::class);
        $oldSheetMet = $this->prophesize(Sheet::class);
        $spot1 = $this->prophesize(Spot::class);
        $spot2 = $this->prophesize(Spot::class);
        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);
        $lastVersion = $this->prophesize(Version::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $locale = 'fr';
        $currentVersion = [
            1 => [
                'request' => 1,
                'fromSheet' => 2,
                'toSheet' => 3,
                'slot' => 9,
                'spot' => 8,
            ],
            4 => [
                'request' => 4,
                'fromSheet' => 2,
                'toSheet' => 4,
                'slot' => 12,
                'spot' => 14,
            ],
        ];
        $lastVersion->getVersion()->willReturn(
            [
                1 => [
                    'request' => 1,
                    'fromSheet' => 2,
                    'toSheet' => 3,
                    'slot' => 10,
                    'spot' => 8,
                ],
                2 => [
                    'request' => 2,
                    'fromSheet' => 2,
                    'toSheet' => 5,
                    'slot' => 12,
                    'spot' => 16,
                ],
            ]
        )
        ;
        $lastVersion->getEvent()->willReturn($event->reveal());
        $lastVersion->getUser()->willReturn($user->reveal());
        $event->getTimeZone()->willReturn('Europe/Paris');
        $sheetMet1->getTitle()->willReturn('sheet title given 1');
        $sheetMet2->getTitle()->willReturn('sheet title given 2');
        $oldSheetMet->getTitle()->willReturn('old sheet title');
        $spot1->getReference()->willReturn('Spot Ref 1');
        $spot2->getReference()->willReturn('Spot Ref 2');
        $slot1->getBegin()->willReturn(new \DateTime('2017-10-10 10:00:00.000', new \DateTimeZone('UTC')));
        $slot2->getBegin()->willReturn(new \DateTime('2017-10-10 14:30:00.000', new \DateTimeZone('UTC')));

        $this->sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([2 => $userSheet->reveal()])
        ;
        $this->sheetRepository
            ->findByIds([3 => 3, 4 => 4, 5 => 5])
            ->shouldBeCalled()
            ->willReturn([3 => $sheetMet1->reveal(), 4 => $sheetMet2->reveal(), 5 => $oldSheetMet->reveal()])
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_MOVED,
                [
                    '%sheetTitle%' => 'sheet title given 1',
                    '%day%' => '10/10/2017',
                    '%hour%' => '12:00',
                    '%spotRef%' => 'Spot Ref 1',
                ],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Meeting moved with sheet title given 1 the 10/10/2017 at 12:00 in Spot Ref 1')
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_ADDED,
                [
                    '%sheetTitle%' => 'sheet title given 2',
                    '%day%' => '10/10/2017',
                    '%hour%' => '16:30',
                    '%spotRef%' => 'Spot Ref 2',
                ],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Meeting added with sheet title given 2 the 10/10/2017 at 16:30 in Spot Ref 2')
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_DELETED,
                ['%sheetTitle%' => 'old sheet title'],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Meeting removed with old sheet title')
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_CHANGED_WITH_MESSAGE,
                ['%message%' => 'I had to delete'],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Message: I had to delete')
        ;
        $this->translator
            ->trans(
                DiffVerbalizer::TRANSLATION_MEETING_CHANGED_WITH_MESSAGE,
                ['%message%' => 'I like to move it move it'],
                DiffVerbalizer::TRANSLATION_DOMAIN,
                $locale
            )
            ->shouldBeCalled()
            ->willReturn('Message: I like to move it move it')
        ;

        $this->meetingSlotRepository
            ->findByIds([9 => 9, 12 => 12])
            ->shouldBeCalled()
            ->willReturn([9 => $slot1->reveal(), 12 => $slot2->reveal()])
        ;
        $this->spotRepository
            ->getSpotsByIds([8 => 8, 14 => 14])
            ->shouldBeCalled()
            ->willReturn([8 => $spot1->reveal(), 14 => $spot2->reveal()])
        ;

        $this->diffChecker
            ->checkTwoVersion(
                [1 => ['request' => 1, 'fromSheet' => 2, 'toSheet' => 3, 'slot' => 9, 'spot' => 8], 4 => ['request' => 4, 'fromSheet' => 2, 'toSheet' => 4, 'slot' => 12, 'spot' => 14]],
                1,
                ['request' => 1, 'fromSheet' => 2, 'toSheet' => 3, 'slot' => 10, 'spot' => 8]
            )->shouldBeCalled()
            ->willReturn(true)
        ;

        $request1 = $this->prophesize(Request::class);
        $request1->getId()->willReturn(1);

        $request2 = $this->prophesize(Request::class);
        $request2->getId()->willReturn(2);

        $request1message = $this->prophesize(Message::class);
        $request1message->getContent()
            ->willReturn('I like to move it move it')
        ;
        $request1message->getRequest()->willReturn($request1->reveal());
        $request1message->getFrom()->willReturn($sheetMet2->reveal());

        $request2message = $this->prophesize(Message::class);
        $request2message->getContent()
            ->willReturn('I had to delete')
        ;
        $request2message->getRequest()->willReturn($request2->reveal());
        $request2message->getFrom()->willReturn($sheetMet2->reveal());

        $this->messageRepository->getUpdateOrDeleteReasonMessageFromRequestIds([2, 1])
            ->shouldBeCalled()
            ->willReturn([$request1message->reveal(), $request2message->reveal()])
        ;

        $diffVerbalizer = new DiffVerbalizer(
            $this->diffChecker->reveal(),
            $this->translator->reveal(),
            $this->sheetRepository->reveal(),
            $this->meetingSlotRepository->reveal(),
            $this->spotRepository->reveal(),
            $this->messageRepository->reveal()
        );
        $result = $diffVerbalizer->verbalizeDiff($lastVersion->reveal(), $currentVersion, $locale);

        $this->assertEquals(
            "Meeting removed with old sheet title Message: I had to delete\nMeeting added with sheet title given 2 the 10/10/2017 at 16:30 in Spot Ref 2\nMeeting moved with sheet title given 1 the 10/10/2017 at 12:00 in Spot Ref 1 Message: I like to move it move it",
            $result
        );
    }
}
