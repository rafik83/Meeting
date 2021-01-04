<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Sheet\ChangeOwner;
use Proximum\Vimeet\Application\Command\Sheet\ChangeOwnerHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\OwnerChangedEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ChangeOwnerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheet,
        $admin,
        $sheetRepository,
        $previousOwner,
        $participant,
        $participant2,
        $participantInfoGuesser,
        $translator,
        $eventDispatcher
    ;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->admin = $this->prophesize(Admin::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->participant2 = $this->prophesize(Participant::class);
        $this->previousOwner = $this->prophesize(User::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
    }
    public function testHandleWithoutChange()
    {
        $this->sheet->getParticipantOwner()->willReturn($this->participant->reveal());
        $this->sheet->getOwner()->willReturn($this->previousOwner->reveal());

        $this->translator->trans(Argument::any())->shouldNotBeCalled();
        $this->sheetRepository->set(Argument::any())->shouldNotBeCalled();
        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();
        $this->participantInfoGuesser->guessParticipantCompleteName(Argument::any())->shouldNotBeCalled();

        $command = new ChangeOwner($this->sheet->reveal(), $this->admin->reveal(), 'fr');
        $handler = new ChangeOwnerHandler(
            $this->sheetRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->translator->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($command);
    }

    public function testHandleWithOwnerNotParticipant()
    {
        $this->sheet->getParticipantOwner()->willReturn(null);
        $this->sheet->getOwner()->willReturn($this->previousOwner->reveal());
        $this->previousOwner->getFirstName()->shouldBeCalled()->willReturn('oldFirstName');
        $this->previousOwner->getLastName()->shouldBeCalled()->willReturn('oldLastName');
        $owner = $this->prophesize(User::class);
        $this->participant->getUser()->shouldBeCalled()->willReturn($owner->reveal());
        $this->sheet->changeOwner($owner->reveal())->shouldBeCalled();

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('newFirstName newLastName')
        ;

        $this->translator->trans('admin.sheet.trace.sheet_owner_changed.comment', [
            '%previousOwner%' => 'oldFirstName oldLastName',
            '%newOwner%' => 'newFirstName newLastName',
        ])->shouldBeCalled()
        ->willReturn('Change owner from oldFirstName oldLastName to newFirstName newLastName');

        $this->sheetRepository->set($this->sheet->reveal())->shouldBeCalled();
        $this->eventDispatcher
            ->dispatch(
                Events::SHEET_OWNER_CHANGED,
                new OwnerChangedEvent(
                    $this->sheet->reveal(),
                    $this->admin->reveal(),
                    $this->previousOwner->reveal(),
                    'Change owner from oldFirstName oldLastName to newFirstName newLastName'
                )
            )
            ->shouldBeCalled()
        ;

        $command = new ChangeOwner($this->sheet->reveal(), $this->admin->reveal(), 'fr');
        $command->owner = $this->participant->reveal();
        $handler = new ChangeOwnerHandler(
            $this->sheetRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->translator->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($command);
    }

    public function testHandle()
    {
        $this->sheet->getParticipantOwner()->willReturn($this->participant->reveal());
        $this->sheet->getOwner()->willReturn($this->previousOwner->reveal());
        $this->previousOwner->getFirstName()->shouldNotBeCalled();
        $this->previousOwner->getLastName()->shouldNotBeCalled();

        $owner = $this->prophesize(User::class);
        $this->participant2->getUser()->shouldBeCalled()->willReturn($owner->reveal());
        $this->sheet->changeOwner($owner->reveal())->shouldBeCalled();

        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('oldFirstName oldLastName')
        ;
        $this->participantInfoGuesser
            ->guessParticipantCompleteName($this->participant2->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn('newFirstName newLastName')
        ;

        $this->translator->trans('admin.sheet.trace.sheet_owner_changed.comment', [
            '%previousOwner%' => 'oldFirstName oldLastName',
            '%newOwner%' => 'newFirstName newLastName',
        ])->shouldBeCalled()
            ->willReturn('Change owner from oldFirstName oldLastName to newFirstName newLastName');

        $this->sheetRepository->set($this->sheet->reveal())->shouldBeCalled();
        $this->eventDispatcher
            ->dispatch(
                Events::SHEET_OWNER_CHANGED,
                new OwnerChangedEvent(
                    $this->sheet->reveal(),
                    $this->admin->reveal(),
                    $this->previousOwner->reveal(),
                    'Change owner from oldFirstName oldLastName to newFirstName newLastName'
                )
            )
            ->shouldBeCalled()
        ;

        $command = new ChangeOwner($this->sheet->reveal(), $this->admin->reveal(), 'fr');
        $command->owner = $this->participant2->reveal();
        $handler = new ChangeOwnerHandler(
            $this->sheetRepository->reveal(),
            $this->participantInfoGuesser->reveal(),
            $this->translator->reveal(),
            $this->eventDispatcher->reveal()
        );
        $handler->handle($command);
    }
}
