<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Submenu;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\ContactsSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\ContactsSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation\NavigationBuilder;

class ContactsSubmenuViewQueryHandlerTest extends TestCase
{
    public function testContactsAvailable()
    {
        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);
        $type->canScanParticipant()->shouldBeCalled()->willReturn(true);

        $sheet->getId()->willReturn(1337);
        $sheet->getType()->willReturn($type->reveal());

        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        $accessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $accessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $navigationBuilder = $this->prophesize(NavigationBuilder::class);
        $navigationBuilder->getRoute(
            'event_contact_index',
            [
                'sheet' => 1337,
            ]
        )->shouldBeCalled()
            ->willReturn('/url/to/contacts');

        $badgeSubmenuViewQueryHandler = new ContactsSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $accessChecker->reveal()
        );
        $result = $badgeSubmenuViewQueryHandler->handle(
            new ContactsSubmenuViewQuery(
                $user->reveal(),
                $event->reveal(),
                'fr',
                $sheet->reveal(),
                'event_contact_index'
            )
        );

        $expectedSubmenuButtonView = new SubmenuButtonView(
            Category::CONTACT_LIST_ICON,
            Category::CONTACT_LIST,
            '/url/to/contacts',
            true,
            false,
            true
        );

        $this->assertEquals($expectedSubmenuButtonView, $result);
    }

    public function testEventNotOpen()
    {
        $sheet = $this->prophesize(Sheet::class);
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        $accessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $accessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(false);

        $navigationBuilder = $this->prophesize(NavigationBuilder::class);

        $badgeSubmenuViewQueryHandler = new ContactsSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $accessChecker->reveal()
        );

        $this->assertNull(
            $badgeSubmenuViewQueryHandler->handle(
                new ContactsSubmenuViewQuery(
                    $user->reveal(),
                    $event->reveal(),
                    'fr',
                    $sheet->reveal(),
                    'event_contact_index'
                )
            )
        );
    }

    public function testParticipantCantScan()
    {
        $sheet = $this->prophesize(Sheet::class);
        $type = $this->prophesize(Type::class);
        $type->canScanParticipant()->shouldBeCalled()->willReturn(false);

        $sheet->getType()->willReturn($type->reveal());

        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);

        $accessChecker = $this->prophesize(EventOpenAccessChecker::class);
        $accessChecker->allowedToAccess($event->reveal())->shouldBeCalled()->willReturn(true);

        $navigationBuilder = $this->prophesize(NavigationBuilder::class);

        $badgeSubmenuViewQueryHandler = new ContactsSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $accessChecker->reveal()
        );

        $this->assertNull(
            $badgeSubmenuViewQueryHandler->handle(
                new ContactsSubmenuViewQuery(
                    $user->reveal(),
                    $event->reveal(),
                    'fr',
                    $sheet->reveal(),
                    'event_contact_index'
                )
            )
        );
    }
}
