<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\ChangeType;
use Proximum\Vimeet\Application\Command\Sheet\ChangeTypeHandler;
use Proximum\Vimeet\Application\Components\Registration\StepManager;
use Proximum\Vimeet\Application\Components\Sheet\Request\EnableDisableManager;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Package\MustSelectPackageEvent;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ChangeTypeHandlerTest extends TestCase
{
    public function testHandleTypesWithDifferentPackages()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $package = new Package($event, 'Package', $date);
        $type    = new Type($event);
        $type->setPackage($package);

        $otherPackage = new Package($event, 'Other package', $date);
        $otherType    = new Type($event);
        $otherType->setPackage($otherPackage);

        $user        = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet       = new Sheet($event, $type, [], $user, $date);
        $participant = new Participant($sheet, $user, [], true);

        $sheet->addParticipant($participant);

        $admin = new Admin(
            'email@email.com',
            'salt',
            'password',
            'fr',
            'John',
            'Doe',
            'ROLE_SUPER_ADMIN',
            new \DateTime()
        );

        $order = new Order(
            $sheet,
            '',
            $date
        );

        $changeType = new ChangeType($sheet, $otherType, $admin, 'fr');

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $orderRepository         = $this->prophesize(OrderRepositoryInterface::class);
        $translator              = $this->prophesize(TranslatorAdapter::class);
        $eventDispatcher         = $this->prophesize(DelayedEventDispatcher::class);
        $registrationStepManager = $this->prophesize(StepManager::class);
        $meetingRepository       = $this->prophesize(MeetingRepositoryInterface::class);
        $enableDisableManager    = $this->prophesize(EnableDisableManager::class);

        $handler = new ChangeTypeHandler(
            $sheetRepository->reveal(),
            $orderRepository->reveal(),
            $meetingRepository->reveal(),
            $enableDisableManager->reveal(),
            $translator->reveal(),
            $eventDispatcher->reveal(),
            $registrationStepManager->reveal(),
            $date
        );
        $handler->handle($changeType);

        $expectedSheet = clone $sheet;
        $expectedSheet->updateType($otherType);

        $sheetRepository->set($expectedSheet)->shouldBeCalled();
        $meetingRepository->countMeetingsOfSheet($sheet)->shouldBeCalled()->willReturn(0);
        $enableDisableManager->update($sheet, false)->shouldBeCalled();

        // Should be called because packages are different
        $orderRepository->findBySheet($expectedSheet)->shouldBeCalled()->willReturn([$order]);

        $registrationStepManager->resetRegistrationStep($participant)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::SHEET_CHANGED_TYPE,
            new SheetChangedTypeEvent($expectedSheet, $admin, $date, '', '', 'fr')
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MUST_SELECT_PACKAGE,
            new MustSelectPackageEvent($expectedSheet)
        )->shouldBeCalled();
    }

    public function testHandleTypesWithSamePackage()
    {
        $date  = new \DateTime();
        $event = EventFactory::createEvent();

        $package = new Package($event, 'Package', $date);
        $type    = new Type($event);
        $type->setPackage($package);
        $otherType = new Type($event);
        $otherType->setPackage($package);

        $user        = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet       = new Sheet($event, $type, [], $user, $date);
        $participant = new Participant($sheet, $user, [], true);

        $sheet->addParticipant($participant);

        $admin       = new Admin(
            'email@email.com',
            'salt',
            'password',
            'fr',
            'John',
            'Doe',
            'ROLE_SUPER_ADMIN',
            new \DateTime()
        );

        $changeType = new ChangeType($sheet, $otherType, $admin, 'fr');

        $sheetRepository         = $this->prophesize(SheetRepositoryInterface::class);
        $orderRepository         = $this->prophesize(OrderRepositoryInterface::class);
        $translator              = $this->prophesize(TranslatorAdapter::class);
        $eventDispatcher         = $this->prophesize(DelayedEventDispatcher::class);
        $registrationStepManager = $this->prophesize(StepManager::class);
        $meetingRepository       = $this->prophesize(MeetingRepositoryInterface::class);
        $enableDisableManager    = $this->prophesize(EnableDisableManager::class);

        $meetingRepository->countMeetingsOfSheet($sheet)->shouldBeCalled()->willReturn(0);
        $enableDisableManager->update($sheet, false)->shouldBeCalled();

        $handler = new ChangeTypeHandler(
            $sheetRepository->reveal(),
            $orderRepository->reveal(),
            $meetingRepository->reveal(),
            $enableDisableManager->reveal(),
            $translator->reveal(),
            $eventDispatcher->reveal(),
            $registrationStepManager->reveal(),
            $date
        );
        $handler->handle($changeType);

        $expectedSheet = clone $sheet;
        $expectedSheet->updateType($otherType);

        $sheetRepository->set($expectedSheet)->shouldBeCalled();

        // Should not be called
        $orderRepository->findBySheet($expectedSheet)->shouldNotBeCalled();

        $registrationStepManager->resetRegistrationStep($participant)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::SHEET_CHANGED_TYPE,
            new SheetChangedTypeEvent($expectedSheet, $admin, $date, '', '', 'fr')
        )->shouldBeCalled();
    }
}
