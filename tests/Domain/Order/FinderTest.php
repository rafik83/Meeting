<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Order;

use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Order\Finder;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class FinderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAllowedToFindFalseAsAdminIsPartner()
    {
        $admin = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'firstname',
            'lastname',
            'ROLE_PARTNER',
            new \DateTime()
        );

        $this->assertEquals(false, Finder::isAllowedToFind($admin));
    }

    public function testIsAllowedToFindTrueAsAdminIsSuperAdmin()
    {
        $admin = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'firstname',
            'lastname',
            'ROLE_SUPER_ADMIN',
            new \DateTime()
        );

        $this->assertEquals(true, Finder::isAllowedToFind($admin));
    }

    public function testIsAllowedToAccessFalseAsAdminIsPartner()
    {
        $date  = new \DateTime();
        $admin = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'firstname',
            'lastname',
            'ROLE_PARTNER',
            $date
        );
        $sheet = SheetFactory::create();
        $billing =  new Order\BillingInfo(
            'gender',
            'lastname',
            'firstname',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            new Address('street', 'zipcode', 'city', 'country'),
            'vatNumber'
        );

        $order = new Order($sheet, true, $billing, '', $date);

        $this->assertEquals(false, Finder::isAllowedToAccess($admin, $order));
    }

    public function testIsAllowedToAccessTrueAsAdminIsSuperAdmin()
    {
        $date  = new \DateTime();
        $admin = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'firstname',
            'lastname',
            'ROLE_SUPER_ADMIN',
            $date
        );
        $sheet   = SheetFactory::create();
        $billing =  new Order\BillingInfo(
            'gender',
            'lastname',
            'firstname',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            new Address('street', 'zipcode', 'city', 'country'),
            'vatNumber'
        );

        $order = new Order($sheet, true, $billing, '', $date);

        $this->assertEquals(true, Finder::isAllowedToAccess($admin, $order));
    }

    public function testIsAllowedToAccessFalseAsAdminIsOrganizerAndHasNoAccessToOrderEvent()
    {
        $date  = new \DateTime();
        $admin = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'firstname',
            'lastname',
            'ROLE_ORGANIZER',
            $date
        );
        $event   = EventFactory::createEvent();
        $sheet   = SheetFactory::create($event);
        $billing =  new Order\BillingInfo(
            'gender',
            'lastname',
            'firstname',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            new Address('street', 'zipcode', 'city', 'country'),
            'vatNumber'
        );

        $order = new Order($sheet, true, $billing, '', $date);

        $this->assertEquals(false, Finder::isAllowedToAccess($admin, $order));
    }

    public function testIsAllowedToAccessTrueAsAdminIsOrganizerAndHasAccessToOrderEvent()
    {
        $date  = new \DateTime();
        $admin = new Admin(
            'email@email.fr',
            'salt',
            'password',
            'fr',
            'firstname',
            'lastname',
            'ROLE_ORGANIZER',
            $date
        );
        $event   = EventFactory::createEvent();
        $sheet   = SheetFactory::create($event);
        $billing =  new Order\BillingInfo(
            'gender',
            'lastname',
            'firstname',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            new Address('street', 'zipcode', 'city', 'country'),
            'vatNumber'
        );
        $admin->addEvent($event);

        $order = new Order($sheet, true, $billing, '', $date);

        $this->assertEquals(true, Finder::isAllowedToAccess($admin, $order));
    }
}
