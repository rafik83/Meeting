<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Command;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\PrepareLeniApiCall;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\PrepareLeniApiCallHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Command\PrepareUserDataForApiCallHandler;
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
    private $userRepository;

    /** @var ObjectProphecy */
    private $participantPlanningFormatter;

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
        $this->dateTime = new \DateTime();
    }

    public function testHandle()
    {
        $this->event1->hasDay()->shouldBeCalled()->willReturn(true);
        $this->event1->isFinished($this->dateTime)->shouldBeCalled()->willReturn(false);

        $this->event2->hasDay()->shouldBeCalled()->willReturn(true);
        $this->event2->isFinished($this->dateTime)->shouldBeCalled()->willReturn(false);

        $extraParamUser1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParamUser2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParamEvent1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParamEvent2 = $this->prophesize(Event\ExtraParameter::class);

        $extraParamMode1 = $this->prophesize(Event\ExtraParameter::class);
        $extraParamMode1->getValue()->shouldBeCalled()->willReturn('save');

        $extraParamMode2 = $this->prophesize(Event\ExtraParameter::class);
        $extraParamMode2->getValue()->shouldBeCalled()->willReturn('save');

        $this->extraParameterRepository
            ->findByEventAndType($this->event1->reveal(), 'leni_user')
            ->shouldBeCalled()
            ->willReturn($extraParamUser1->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event1->reveal(), 'leni_event')
            ->shouldBeCalled()
            ->willReturn($extraParamEvent1->reveal())
        ;

        $this->extraParameterRepository
            ->findByEventAndType($this->event2->reveal(), 'leni_user')
            ->shouldBeCalled()
            ->willReturn($extraParamUser2->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event2->reveal(), 'leni_event')
            ->shouldBeCalled()
            ->willReturn($extraParamEvent2->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event1->reveal(), 'leni_mode')
            ->shouldBeCalled()
            ->willReturn($extraParamMode1->reveal())
        ;
        $this->extraParameterRepository
            ->findByEventAndType($this->event2->reveal(), 'leni_mode')
            ->shouldBeCalled()
            ->willReturn($extraParamMode2->reveal())
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
            ->findWithSheetByEvent($this->event1->reveal())
            ->shouldBeCalled()
            ->willReturn([$user1FromEvent1->reveal(), $user2FromEvent1->reveal()])
        ;

        $this->userRepository
            ->findWithSheetByEvent($this->event2->reveal())
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


        $prepareUserDataForApiCallHandler = $this->prophesize(PrepareUserDataForApiCallHandler::class);

        $prepareLeniApiCallHandler = new PrepareLeniApiCallHandler(
            $this->eventRepository->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->extraDataRepository->reveal(),
            $this->userRepository->reveal(),
            $this->participantPlanningFormatter->reveal(),
            $prepareUserDataForApiCallHandler->reveal(),
            $this->dateTime
        );

        $prepareLeniApiCallHandler->handle(new PrepareLeniApiCall());
    }
}
