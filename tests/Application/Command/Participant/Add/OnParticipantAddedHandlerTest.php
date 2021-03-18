<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant\Add;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Participant\Add\OnParticipantAdded;
use Proximum\Vimeet\Application\Command\Participant\Add\OnParticipantAddedHandler;
use Proximum\Vimeet\Application\Command\UserEventView\Update;
use Proximum\Vimeet\Application\Components\Token\User\ActivateAccountTokenGenerator;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetAddParticipantEvent;
use Proximum\Vimeet\Application\Event\User\ActivateAccountEvent;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant\OnParticipantAdded as ComexposiumOnParticipantAdded;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\ParticipantOfSheetWithPackageParticipantAndPlanningDisabled;
use Proximum\Vimeet\Domain\Repository\Event\ExtraParameterRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class OnParticipantAddedHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $participant;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $adder;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    /** @var ObjectProphecy */
    private $activateAccountTokenGenerator;

    /** @var ObjectProphecy */
    private $extraParameterRepository;

    /** @var ObjectProphecy */
    private $commandBus;

    /** @var ObjectProphecy|ParticipantOfSheetWithPackageParticipantAndPlanningDisabled */
    private $participantOfSheetWithPackageParticipantAndPlanningDisabled;

    /** @var OnParticipantAddedHandler */
    private $handler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->adder = $this->prophesize(User::class);
        $this->user = $this->prophesize(User::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getEvent()->willReturn($this->event->reveal());

        $this->participant = $this->prophesize(Participant::class);
        $this->participant->getSheet()->willReturn($this->sheet->reveal());
        $this->participant->getUser()->willReturn($this->user->reveal());
        $this->participant->getEvent()->willReturn($this->event->reveal());

        $this->eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $this->activateAccountTokenGenerator = $this->prophesize(ActivateAccountTokenGenerator::class);
        $this->extraParameterRepository = $this->prophesize(ExtraParameterRepositoryInterface::class);
        $this->commandBus = $this->prophesize(CommandBusInterface::class);
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled = $this->prophesize(
            ParticipantOfSheetWithPackageParticipantAndPlanningDisabled::class
        );

        $this->handler = new OnParticipantAddedHandler(
            $this->eventDispatcher->reveal(),
            $this->activateAccountTokenGenerator->reveal(),
            $this->extraParameterRepository->reveal(),
            $this->commandBus->reveal(),
            $this->participantOfSheetWithPackageParticipantAndPlanningDisabled->reveal()
        );
    }

    public function testHandleUserOwner()
    {
        $this->eventDispatcher->dispatch(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->activateAccountTokenGenerator->generate(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldNotBeCalled()
        ;

        $this->sheet->isOwner($this->user->reveal())->shouldBeCalled()->willReturn(true);

        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($this->participant->reveal())
            ->shouldBeCalled()
        ;

        $this->handler->handle(new OnParticipantAdded($this->participant->reveal(), $this->adder->reveal()));
    }

    public function testHandleUserActive()
    {
        $this->participant->getLocale()->willReturn('fr');
        $this->user->isActive()->willReturn(true);

        $this->eventDispatcher
            ->dispatch(
                Events::USER_PROFILE_COMPLETED,
                new CompleteProfileEvent(
                    $this->user->reveal(),
                    $this->event->reveal(),
                    $this->participant->reveal(),
                    'fr'
                )
            )
            ->shouldBeCalled()
        ;

        $this->eventDispatcher
            ->dispatch(
                Events::SHEET_ADD_PARTICIPANT_CONFIRMATION,
                new SheetAddParticipantEvent(
                    $this->sheet->reveal(),
                    $this->participant->reveal(),
                    $this->adder->reveal()
                )
            )
            ->shouldBeCalled()
        ;
        $this->activateAccountTokenGenerator->generate(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->sheet->isOwner($this->user->reveal())->shouldBeCalled()->willReturn(false);

        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($this->participant->reveal())
            ->shouldBeCalled()
        ;

        $this->handler->handle(new OnParticipantAdded($this->participant->reveal(), $this->adder->reveal()));
    }

    public function testHandleUserNotActive()
    {
        $this->participant->getLocale()->willReturn('fr');
        $this->user->isActive()->willReturn(false);
        $token = $this->prophesize(User\ActivateAccountToken::class);

        $this->eventDispatcher
            ->dispatch(
                Events::USER_ACCOUNT_ACTIVATED,
                new ActivateAccountEvent(
                    $this->user->reveal(),
                    $this->adder->reveal(),
                    $this->event->reveal(),
                    $token->reveal(),
                    $this->sheet->reveal()
                )
            )
            ->shouldBeCalled()
        ;

        $this->eventDispatcher
            ->dispatch(
                Events::SHEET_ADD_PARTICIPANT_CONFIRMATION,
                new SheetAddParticipantEvent(
                    $this->sheet->reveal(),
                    $this->participant->reveal(),
                    $this->adder->reveal()
                )
            )
            ->shouldBeCalled()
        ;
        $this->activateAccountTokenGenerator->generate($this->user->reveal(), $this->sheet->reveal())->shouldBeCalled()->willReturn($token->reveal());
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->sheet->isOwner($this->user->reveal())->shouldBeCalled()->willReturn(false);

        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($this->participant->reveal())
            ->shouldBeCalled()
        ;

        $this->handler->handle(new OnParticipantAdded($this->participant->reveal(), $this->adder->reveal()));
    }

    public function testHandleSSO()
    {
        $extraParameter = $this->prophesize(Event\ExtraParameter::class);
        $this->participant->getLocale()->willReturn('fr');
        $this->user->isActive()->willReturn(false);

        $this->eventDispatcher
            ->dispatch(
                Events::SHEET_ADD_PARTICIPANT_CONFIRMATION,
                new SheetAddParticipantEvent(
                    $this->sheet->reveal(),
                    $this->participant->reveal(),
                    $this->adder->reveal()
                )
            )
            ->shouldBeCalled()
        ;
        $this->activateAccountTokenGenerator->generate(Argument::any(), Argument::any())->shouldNotBeCalled();
        $this->extraParameterRepository
            ->findByEventAndType($this->event->reveal(), Type::TYPE_COMEXPOSIUM_SSO_ENABLED)
            ->shouldBeCalled()
            ->willReturn($extraParameter->reveal())
        ;

        $this->commandBus
            ->handle(new ComexposiumOnParticipantAdded($this->event->reveal(), $this->participant->reveal()))
            ->shouldBeCalled()
        ;

        $this->commandBus
            ->handle(new Update($this->user->reveal(), $this->event->reveal()))
            ->shouldBeCalled()
        ;

        $this->sheet->isOwner($this->user->reveal())->shouldBeCalled()->willReturn(false);

        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($this->participant->reveal())
            ->shouldBeCalled()
        ;

        $this->handler->handle(new OnParticipantAdded($this->participant->reveal(), $this->adder->reveal()));
    }
}
