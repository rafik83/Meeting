<?php

namespace Proximum\Vimeet\Tests\Application\Command\Invoice;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Invoice\Find;
use Proximum\Vimeet\Application\Command\Invoice\FindHandler;
use Proximum\Vimeet\Application\Command\Invoice\FindResult;
use Proximum\Vimeet\Application\Exception\Invoice\InvalidNumeroInvoiceException;
use Proximum\Vimeet\Application\Exception\Invoice\InvoiceNotFoundException;
use Proximum\Vimeet\Application\Exception\Invoice\IsNotAllowedToFindInvoiceException;
use Proximum\Vimeet\Domain\Invoice\Numero\InvoiceNumeroView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;

class FindHandlerTest extends TestCase
{
    public function testHandleNotAllowedToFind()
    {
        $this->expectException(IsNotAllowedToFindInvoiceException::class);
        $numero = 'Vi2017-1234';

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(true);
        $admin->getId()->willReturn(1);

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);

        $handler = new FindHandler($invoiceRepository->reveal());
        $handler->handle(new Find($admin->reveal(), $numero));
    }

    public function testHandleNotValidNumero()
    {
        $this->expectException(InvalidNumeroInvoiceException::class);
        $numero = 'not-valid-numero';

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(false);

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);

        $handler = new FindHandler($invoiceRepository->reveal());
        $handler->handle(new Find($admin->reveal(), $numero));
    }

    public function testHandleWithoutInvoice()
    {
        $this->expectException(InvoiceNotFoundException::class);
        $numero = 'Vi2017-1234';

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(false);

        $invoiceNumeroView = new InvoiceNumeroView('Vi', 2017, 1234);

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $invoiceRepository->findByNumero($invoiceNumeroView)->shouldBeCalled()->willReturn([]);

        $handler = new FindHandler($invoiceRepository->reveal());
        $handler->handle(new Find($admin->reveal(), $numero));
    }

    public function testHandleWithInvoiceButNotAccessible()
    {
        $this->expectException(InvoiceNotFoundException::class);
        $numero = 'Vi2017-1234';

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(false);
        $admin->hasAccessToAllEvent()->willReturn(false);

        $invoiceNumeroView = new InvoiceNumeroView('Vi', 2017, 1234);
        $invoice1 = $this->prophesize(Invoice::class);
        $sheet1   = $this->prophesize(Sheet::class);
        $event1   = $this->prophesize(Event::class);
        $invoice1->getSheet()->willReturn($sheet1);
        $sheet1->getEvent()->willReturn($event1);

        $invoice2 = $this->prophesize(Invoice::class);
        $sheet2   = $this->prophesize(Sheet::class);
        $event2   = $this->prophesize(Event::class);
        $invoice2->getSheet()->willReturn($sheet2);
        $sheet2->getEvent()->willReturn($event2);

        $admin->hasEvent($event1->reveal())->willReturn(false);
        $admin->hasEvent($event2->reveal())->willReturn(false);

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $invoiceRepository
            ->findByNumero($invoiceNumeroView)
            ->shouldBeCalled()
            ->willReturn([$invoice1->reveal(), $invoice2->reveal()])
        ;

        $handler = new FindHandler($invoiceRepository->reveal());
        $handler->handle(new Find($admin->reveal(), $numero));
    }

    public function testHandle()
    {
        $numero = 'Vi2017-1234';

        $admin = $this->prophesize(Admin::class);
        $admin->isPartner()->willReturn(false);
        $admin->hasAccessToAllEvent()->willReturn(false);

        $invoiceNumeroView = new InvoiceNumeroView('Vi', 2017, 1234);
        $invoice1 = $this->prophesize(Invoice::class);
        $sheet1   = $this->prophesize(Sheet::class);
        $event1   = $this->prophesize(Event::class);
        $invoice1->getSheet()->willReturn($sheet1);
        $sheet1->getEvent()->willReturn($event1);

        $invoice2 = $this->prophesize(Invoice::class);
        $sheet2   = $this->prophesize(Sheet::class);
        $event2   = $this->prophesize(Event::class);
        $invoice2->getSheet()->willReturn($sheet2);
        $sheet2->getEvent()->willReturn($event2);

        $admin->hasEvent($event1->reveal())->willReturn(true);
        $admin->hasEvent($event2->reveal())->willReturn(false);

        $invoiceRepository = $this->prophesize(InvoiceRepositoryInterface::class);
        $invoiceRepository
            ->findByNumero($invoiceNumeroView)
            ->shouldBeCalled()
            ->willReturn([$invoice1->reveal(), $invoice2->reveal()])
        ;

        $handler = new FindHandler($invoiceRepository->reveal());
        $result = $handler->handle(new Find($admin->reveal(), $numero));

        $expected = new FindResult([$sheet1->reveal()]);

        $this->assertEquals($expected, $result);
    }
}
