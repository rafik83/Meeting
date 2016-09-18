<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\ChangeType;
use Proximum\Vimeet\Application\Command\Sheet\ChangeTypeHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetChangedTypeEvent;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ChangeTypeHandlerTest extends \PHPUnit_Framework_TestCase
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

        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, $date);
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
            true,
            new Order\BillingInfo(
                'gender',
                'firstname',
                'lastname',
                'position',
                'phone',
                'mobile',
                'email',
                'company',
                new Address('street', 'zipcode', 'city', 'country'),
                'vatNumber'
            ),
            '',
            $date
        );

        $changeType = new ChangeType($sheet, $otherType, $admin, $date, 'fr');

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $translator      = $this->prophesize(TranslatorAdapter::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $handler = new ChangeTypeHandler(
            $sheetRepository->reveal(),
            $orderRepository->reveal(),
            $translator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($changeType);

        $expectedSheet = clone $sheet;
        $expectedSheet->updateType($otherType);

        $sheetRepository->set($expectedSheet)->shouldBeCalled();

        // Should be called because packages are different
        $orderRepository->findBySheet($expectedSheet)->shouldBeCalled()->willReturn([$order]);

        $eventDispatcher->dispatch(
            Events::SHEET_CHANGED_TYPE,
            new SheetChangedTypeEvent($expectedSheet, $admin, $date, '')
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

        $user  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet = new Sheet($event, $type, [], $user, $date);
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
            true,
            new Order\BillingInfo(
                'gender',
                'firstname',
                'lastname',
                'position',
                'phone',
                'mobile',
                'email',
                'company',
                new Address('street', 'zipcode', 'city', 'country'),
                'vatNumber'
            ),
            '',
            $date
        );

        $changeType = new ChangeType($sheet, $otherType, $admin, $date, 'fr');

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $orderRepository = $this->prophesize(OrderRepositoryInterface::class);
        $translator      = $this->prophesize(TranslatorAdapter::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $handler = new ChangeTypeHandler(
            $sheetRepository->reveal(),
            $orderRepository->reveal(),
            $translator->reveal(),
            $eventDispatcher->reveal()
        );
        $handler->handle($changeType);

        $expectedSheet = clone $sheet;
        $expectedSheet->updateType($otherType);

        $sheetRepository->set($expectedSheet)->shouldBeCalled();

        // Should not be called
        $orderRepository->findBySheet($expectedSheet)->shouldNotBeCalled();

        $eventDispatcher->dispatch(
            Events::SHEET_CHANGED_TYPE,
            new SheetChangedTypeEvent($expectedSheet, $admin, $date, '')
        )->shouldBeCalled();
    }
}
