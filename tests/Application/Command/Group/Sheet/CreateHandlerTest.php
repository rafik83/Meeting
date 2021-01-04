<?php

namespace Proximum\Vimeet\Tests\Application\Command\Group\Sheet;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Group\Sheet\Create;
use Proximum\Vimeet\Application\Command\Group\Sheet\CreateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Group\Sheet\SheetCreatedByManagerEvent;
use Proximum\Vimeet\Application\Event\Package\MustSelectPackageEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\SheetInfoSetter;

class CreateHandlerTest extends TestCase
{
    public function testHandle()
    {
        $newTitle = 'new title';
        $originalSheet = $this->prophesize(Sheet::class);
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $owner = $this->prophesize(User::class);
        $group = $this->prophesize(Sheet\Group::class);
        $spot = $this->prophesize(Spot::class);

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant1->getUser()->willReturn($user1->reveal());
        $participant1->getData()->willReturn(['6789' => ['content' => 'data1']]);
        $participant2->getUser()->willReturn($user2->reveal());
        $participant2->getData()->willReturn(['6789' => ['content' => 'data2']]);
        $participant1->isRegistrationComplete()->willReturn(true);
        $participant1->getRegistrationStep()->willReturn(2);
        $participant2->isRegistrationComplete()->willReturn(false);
        $participant2->getRegistrationStep()->willReturn(1);

        $originalSheet->getType()->willReturn($type->reveal());
        $originalSheet->getEvent()->willReturn($event->reveal());
        $originalSheet->getData()->willReturn(['12345' => ['content' => 'data']]);
        $originalSheet->getOwner()->willReturn($owner->reveal());
        $originalSheet->getGroup()->willReturn($group->reveal());
        $originalSheet->getRegistrationData()->willReturn(['54321' => ['content' => 'sheet title']]);
        $originalSheet->getFollower()->willReturn(null);
        $originalSheet->getSpot()->willReturn($spot->reveal());
        $originalSheet->getParticipants()->willReturn(new ArrayCollection([$participant1->reveal(), $participant2->reveal()]));

        $date = new \DateTime();

        // Mock
        $sheetInfoSetter = $this->prophesize(SheetInfoSetter::class);
        $sheetInfoSetter->setSheetTitle(Argument::that(function (Sheet $input) use ($event, $type, $group, $spot) {
            return $input->getRegistrationData() === ['54321' => ['content' => 'sheet title']]
                && $input->getSpot() === $spot->reveal()
                && $input->getEvent() === $event->reveal()
                && $input->getType() === $type->reveal()
                && $input->getGroup() === $group->reveal();
        }), $newTitle)->shouldBeCalled();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->add(Argument::that(function (Sheet $input) use ($event, $type, $group, $spot) {
            return $input->getRegistrationData() === ['54321' => ['content' => 'sheet title']]
                && $input->getSpot() === $spot->reveal()
                && $input->getEvent() === $event->reveal()
                && $input->getType() === $type->reveal()
                && $input->getGroup() === $group->reveal();
        }))->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->add(Argument::that(function (Participant $input) use ($user1) {
            return $input->getData() === ['6789' => ['content' => 'data1']]
                && $input->getUser() === $user1->reveal()
                && 2 === $input->getRegistrationStep()
                && true === $input->isRegistrationComplete();
        }))->shouldBeCalled();
        $participantRepository->add(Argument::that(function (Participant $input) use ($user2) {
            return $input->getData() === ['6789' => ['content' => 'data2']]
            && $input->getUser() === $user2->reveal()
            && 1 === $input->getRegistrationStep()
            && false === $input->isRegistrationComplete();
        }))->shouldBeCalled();

        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $delayedEventDispatcher->dispatch(
            Events::SHEET_UPDATED,
            Argument::that(function ($input) {return $input instanceof SheetUpdatedEvent; })
        )->shouldBeCalled();
        $delayedEventDispatcher->dispatch(
            Events::MUST_SELECT_PACKAGE,
            Argument::that(function ($input) {return $input instanceof MustSelectPackageEvent; })
        )->shouldBeCalled();
        $delayedEventDispatcher->dispatch(
            Events::SHEET_CREATE_BY_GROUP_MANAGER,
            Argument::that(function ($input) {return $input instanceof SheetCreatedByManagerEvent; })
        )->shouldBeCalled();

        // Handler
        $handler = new CreateHandler(
            $sheetRepository->reveal(),
            $sheetInfoSetter->reveal(),
            $participantRepository->reveal(),
            $delayedEventDispatcher->reveal(),
            $date
        );

        $command = new Create();
        $command->sheet = $originalSheet->reveal();
        $command->title = $newTitle;

        $handler->handle($command);
    }
}
