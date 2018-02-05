<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ThirdParty\LENI\LeniApiCallJobQueueInterface;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Command\PrepareLeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Command\PrepareLeniApiCallHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningDayView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class PrepareLeniApiCallHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event1;

    /** @var ObjectProphecy */
    private $event2;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ObjectProphecy */
    private $eventRepository;

    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $extraDataRepository;

    /** @var ObjectProphecy */
    private $leniApiCallJobQueue;

    /** @var ObjectProphecy */
    private $userRepository;

    /** @var ObjectProphecy */
    private $participantPlanningFormatter;

    /** @var ObjectProphecy */
    private $leniUserViewQueryHandler;

    /** @var ObjectProphecy */
    private $serializerAdapter;

    public function setUp()
    {
        $this->event1 = $this->prophesize(Event::class);
        $this->event2 = $this->prophesize(Event::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $this->eventRepository
            ->findEventWithParameters(['leni_user', 'leni_event'])
            ->willReturn([$this->event1->reveal(), $this->event2->reveal()])
        ;

        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $this->leniUserViewQueryHandler = $this->prophesize(LeniUserViewQueryHandler::class);
        $this->serializerAdapter = $this->prophesize(SerializerAdapterInterface::class);
        $this->leniApiCallJobQueue = $this->prophesize(LeniApiCallJobQueueInterface::class);
        $this->dateTime = new \DateTime();
    }

    public function testHandle()
    {
        $this->event1->hasDay()->shouldBeCalled()->willReturn(true);
        $this->event1->isFinished($this->dateTime)->shouldBeCalled()->willReturn(false);

        $this->event2->hasDay()->shouldBeCalled()->willReturn(true);
        $this->event2->isFinished($this->dateTime)->shouldBeCalled()->willReturn(false);

        $this->extraParameterRepository
            ->findByEventAndType($this->event1->reveal(), 'leni_user')
            ->shouldBeCalled()
            ->willReturn('leni user 1')
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event1->reveal(), 'leni_event')
            ->shouldBeCalled()
            ->willReturn('leni event 1')
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event2->reveal(), 'leni_user')
            ->shouldBeCalled()
            ->willReturn('leni user 2')
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event2->reveal(), 'leni_event')
            ->shouldBeCalled()
            ->willReturn('leni event 2')
        ;

        $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($this->event1->reveal())->shouldBeCalled();
        $this->participantPlanningFormatter->resetPlanningHandlerForEvent($this->event1->reveal())->shouldBeCalled();

        $this->participantPlanningFormatter->preloadPlanningHandlerForEvent($this->event2->reveal())->shouldBeCalled();
        $this->participantPlanningFormatter->resetPlanningHandlerForEvent($this->event2->reveal())->shouldBeCalled();

        $user1FromEvent1 = $this->prophesize(User::class);
        $user1FromEvent1->getId()->shouldBeCalled()->willReturn(11);
        $user2FromEvent1 = $this->prophesize(User::class);
        $user2FromEvent1->getId()->shouldBeCalled()->willReturn(12);

        $this->userRepository
            ->findByEvent($this->event1->reveal())
            ->shouldBeCalled()
            ->willReturn([$user1FromEvent1->reveal(), $user2FromEvent1->reveal()])
        ;

        $this->userRepository
            ->findByEvent($this->event2->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $extraDataUser2 = new ExtraData(
            $user2FromEvent1->reveal(),
            $this->event1->reveal(),
            'leni_fingerprint_pending',
            'a:1:{s:7:"Societe";s:12:"Sheet user 2";}',
            $this->dateTime
        );

        $this->extraDataRepository
            ->getExtraDataForEventAndName($this->event1->reveal(), 'leni_fingerprint')
            ->shouldBeCalled()
            ->willReturn([$extraDataUser2])
        ;

        $this->extraDataRepository
            ->getExtraDataForEventAndName($this->event2->reveal(), 'leni_fingerprint')
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $leniUserViewUser1 = new LeniUserView(
            11,
            true,
            'Sheet user 1',
            1337,
            null,
            'user1@example.com',
            'man',
            'John',
            'Doe',
            'Developer',
            '+33565675776',
            '+33565675778',
            'FR',
            'fr',
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('day one'),
                    new LeniPlanningDayView('day two'),
                ],
                'Unallocated'
            ),
            null
        );

        $this
            ->leniUserViewQueryHandler->handle(
                new LeniUserViewQuery($this->event1->reveal(), $user1FromEvent1->reveal(), null)
            )
            ->shouldBeCalled()
            ->willReturn($leniUserViewUser1)
        ;

        $this->serializerAdapter
            ->normalize($leniUserViewUser1)
            ->shouldBeCalled()
            ->willReturn(['Societe' => 'Sheet user 1'])
        ;

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event1->reveal(),
                'leni_fingerprint_pending',
                $user1FromEvent1->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $extraDataUser1 = new ExtraData(
            $user1FromEvent1->reveal(),
            $this->event1->reveal(),
            'leni_fingerprint_pending',
            'a:1:{s:7:"Societe";s:12:"Sheet user 1";}',
            $this->dateTime
        );

        $this->extraDataRepository
            ->add($extraDataUser1)
            ->shouldBeCalled()
        ;

        $this->leniApiCallJobQueue
            ->createJob($extraDataUser1)
            ->shouldBeCalled()
        ;

        $leniUserViewUser2 = new LeniUserView(
            22,
            true,
            'Sheet user 2',
            1337,
            null,
            'user2@example.com',
            'woman',
            'Julia',
            'Roberts',
            'Actress',
            '+666666666',
            '+999999999',
            'US',
            'en',
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('day one'),
                    new LeniPlanningDayView('day two'),
                ],
                'No unallocated'
            ),
            'whatever-id'
        );

        $this
            ->leniUserViewQueryHandler->handle(
                new LeniUserViewQuery($this->event1->reveal(), $user2FromEvent1->reveal(), $extraDataUser2)
            )
            ->shouldBeCalled()
            ->willReturn($leniUserViewUser2)
        ;

        $this->serializerAdapter
            ->normalize($leniUserViewUser2)
            ->shouldBeCalled()
            ->willReturn(['Societe' => 'Sheet user 2'])
        ;

        $this->extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $this->event1->reveal(),
                'leni_fingerprint_pending',
                $user2FromEvent1->reveal()
            )
            ->shouldNotBeCalled()
        ;

        $this->extraDataRepository
            ->add($extraDataUser2)
            ->shouldNotBeCalled()
        ;

        $this->leniApiCallJobQueue
            ->createJob($extraDataUser2)
            ->shouldNotBeCalled()
        ;

        $prepareLeniApiCallHandler = new PrepareLeniApiCallHandler(
            $this->eventRepository->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->userRepository->reveal(),
            $this->participantPlanningFormatter->reveal(),
            $this->leniUserViewQueryHandler->reveal(),
            $this->serializerAdapter->reveal(),
            $this->leniApiCallJobQueue->reveal(),
            $this->dateTime
        );

        $prepareLeniApiCallHandler->handle(new PrepareLeniApiCall());
    }
}
