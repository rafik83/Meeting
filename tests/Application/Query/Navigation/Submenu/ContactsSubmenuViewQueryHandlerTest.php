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
use Proximum\Vimeet\Application\Components\Contact\CanAccessToContacts;
use Proximum\Vimeet\Application\Components\Navigation\Category;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\ContactsSubmenuViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Submenu\ContactsSubmenuViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\SubmenuButtonView;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\CanScanParticipant;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Navigation\NavigationBuilder;

class ContactsSubmenuViewQueryHandlerTest extends TestCase
{
    public function testContactsAvailable()
    {
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $sheet->getId()->shouldBeCalled()->willReturn(1337);
        $canAccessToContacts = $this->prophesize(CanAccessToContacts::class);
        $canAccessToContacts->isSatisfiedBy($event->reveal(), $user->reveal(), $sheet->reveal())->shouldBeCalled()->willReturn(true);

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
            $canAccessToContacts->reveal()
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
            null,
            true
        );

        self::assertEquals($expectedSubmenuButtonView, $result);
    }

    public function testEventNotOpen()
    {
        $user = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $canAccessToContacts = $this->prophesize(CanAccessToContacts::class);
        $canAccessToContacts->isSatisfiedBy($event->reveal(), $user->reveal(), $sheet->reveal())->shouldBeCalled()->willReturn(false);

        $navigationBuilder = $this->prophesize(NavigationBuilder::class);

        $badgeSubmenuViewQueryHandler = new ContactsSubmenuViewQueryHandler(
            $navigationBuilder->reveal(),
            $canAccessToContacts->reveal()
        );

        self::assertNull(
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
