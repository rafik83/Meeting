<?php

namespace Proximum\Vimeet\Tests\Domain\Invoice\Numero;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Invoice\Finder;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;

class FinderTest extends TestCase
{
    public function testIsAllowedToFindFalseAsAdminIsPartner()
    {
        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(true);

        $this->assertEquals(false, Finder::isAllowedToFind($admin->reveal()));
    }

    public function testIsAllowedToFindTrueAsAdminIsSuperAdmin()
    {
        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(false);

        $this->assertEquals(true, Finder::isAllowedToFind($admin->reveal()));
    }

    public function testIsAllowedToAccessFalseAsAdminIsPartner()
    {
        $admin   = $this->prophesize(Admin::class);
        $invoice = $this->prophesize(Invoice::class);
        $sheet   = $this->prophesize(Sheet::class);
        $event   = $this->prophesize(Event::class);
        $invoice->getSheet()->willReturn($sheet);
        $sheet->getEvent()->willReturn($event);
        $admin->isPartner()->willReturn(true);

        $this->assertEquals(false, Finder::isAllowedToAccess($admin->reveal(), $invoice->reveal()));
    }

    public function testIsAllowedToAccessTrueAsAdminIsSuperAdmin()
    {
        $admin   = $this->prophesize(Admin::class);
        $invoice = $this->prophesize(Invoice::class);
        $sheet   = $this->prophesize(Sheet::class);
        $event   = $this->prophesize(Event::class);
        $invoice->getSheet()->willReturn($sheet);
        $sheet->getEvent()->willReturn($event);
        $admin->isPartner()->willReturn(false);
        $admin->hasAccessToAllEvent()->willReturn(true);

        $this->assertEquals(true, Finder::isAllowedToAccess($admin->reveal(), $invoice->reveal()));
    }

    public function testIsAllowedToAccessFalseAsAdminIsOrganizerAndHasNoAccessToInvoiceEvent()
    {
        $admin   = $this->prophesize(Admin::class);
        $invoice = $this->prophesize(Invoice::class);
        $sheet   = $this->prophesize(Sheet::class);
        $event   = $this->prophesize(Event::class);
        $invoice->getSheet()->willReturn($sheet);
        $sheet->getEvent()->willReturn($event);
        $admin->isPartner()->willReturn(false);
        $admin->hasAccessToAllEvent()->willReturn(false);
        $admin->hasEvent($event->reveal())->willReturn(false);

        $this->assertEquals(false, Finder::isAllowedToAccess($admin->reveal(), $invoice->reveal()));
    }

    public function testIsAllowedToAccessTrueAsAdminIsOrganizerAndHasAccessToInvoiceEvent()
    {
        $admin   = $this->prophesize(Admin::class);
        $invoice = $this->prophesize(Invoice::class);
        $sheet   = $this->prophesize(Sheet::class);
        $event   = $this->prophesize(Event::class);
        $invoice->getSheet()->willReturn($sheet);
        $sheet->getEvent()->willReturn($event);
        $admin->isPartner()->willReturn(false);
        $admin->hasAccessToAllEvent()->willReturn(false);
        $admin->hasEvent($event->reveal())->willReturn(true);

        $this->assertEquals(true, Finder::isAllowedToAccess($admin->reveal(), $invoice->reveal()));
    }
}
