<?php

namespace Proximum\Vimeet\Tests\Application\Command\Event\Find;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Event\Find\Find;
use Proximum\Vimeet\Application\Command\Event\Find\FindHandler;
use Proximum\Vimeet\Application\Command\Invoice\Find as InvoiceFind;
use Proximum\Vimeet\Application\Command\Invoice\FindHandler as InvoiceFindHandler;
use Proximum\Vimeet\Application\Command\Invoice\FindResult as InvoiceFindResult;
use Proximum\Vimeet\Application\Command\Order\Find as OrderFind;
use Proximum\Vimeet\Application\Command\Order\FindHandler as OrderFindHandler;
use Proximum\Vimeet\Application\Command\Order\FindResult as OrderFindResult;
use Proximum\Vimeet\Application\Exception\Event\InvalidFindException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class FindHandlerTest extends TestCase
{
    public function testHandleException()
    {
        $this->expectException(InvalidFindException::class);
        $admin = $this->prophesize(Admin::class);

        $orderFindHandler = $this->prophesize(OrderFindHandler::class);
        $invoiceFindHandler = $this->prophesize(InvoiceFindHandler::class);

        $find = new Find($admin->reveal());
        $find->type   = 'fail';
        $find->numero = 'toto';

        $handler = new FindHandler($orderFindHandler->reveal(), $invoiceFindHandler->reveal());
        $handler->handle($find);
    }

    public function testHandleInvoice()
    {
        $admin = $this->prophesize(Admin::class);

        $orderFindHandler = $this->prophesize(OrderFindHandler::class);

        $sheet = $this->prophesize(Sheet::class);
        $invoiceFindResult = new InvoiceFindResult([$sheet->reveal()]);
        $invoiceFindHandler = $this->prophesize(InvoiceFindHandler::class);
        $invoiceFindHandler->handle(new InvoiceFind($admin->reveal(), 'toto'))->shouldBeCalled()->willReturn($invoiceFindResult);

        $find = new Find($admin->reveal());
        $find->type   = Find::FIND_INVOICE;
        $find->numero = 'toto';

        $handler = new FindHandler($orderFindHandler->reveal(), $invoiceFindHandler->reveal());
        $result = $handler->handle($find);

        $expected = new InvoiceFindResult([$sheet->reveal()]);

        $this->assertEquals($expected, $result);
    }

    public function testHandleOrder()
    {
        $admin = $this->prophesize(Admin::class);

        $sheet = $this->prophesize(Sheet::class);
        $orderFindResult = new OrderFindResult($sheet->reveal());
        $orderFindHandler = $this->prophesize(OrderFindHandler::class);
        $orderFindHandler->handle(new OrderFind($admin->reveal(), 'toto'))->shouldBeCalled()->willReturn($orderFindResult);

        $invoiceFindHandler = $this->prophesize(InvoiceFindHandler::class);

        $find = new Find($admin->reveal());
        $find->type   = Find::FIND_ORDER;
        $find->numero = 'toto';

        $handler = new FindHandler($orderFindHandler->reveal(), $invoiceFindHandler->reveal());
        $result = $handler->handle($find);

        $expected = new OrderFindResult($sheet->reveal());

        $this->assertEquals($expected, $result);
    }
}
