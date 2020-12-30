<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\CustomRowView;

class CustomRowViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $adminLocale = 'fr';
        $index       = 2;
        $translator  = $this->prophesize(TranslatorInterface::class);

        $translator->trans('order.column.customRow.title', ['%customRowIndex%' => 2], 'export', $adminLocale)->shouldBeCalled()->willReturn('2 title');
        $translator->trans('order.column.customRow.unitPrice', ['%customRowIndex%' => 2], 'export', $adminLocale)->shouldBeCalled()->willReturn('2 unitPrice');
        $translator->trans('order.column.customRow.quantity', ['%customRowIndex%' => 2], 'export', $adminLocale)->shouldBeCalled()->willReturn('2 quantity');
        $translator->trans('order.column.customRow.total', ['%customRowIndex%' => 2], 'export', $adminLocale)->shouldBeCalled()->willReturn('2 total');

        $handler = new CustomRowViewQueryHandler($translator->reveal());
        $result = $handler->handle(new CustomRowViewQuery($index, $adminLocale));

        $expected = new CustomRowView(2, '2 title', '2 unitPrice', '2 quantity', '2 total');
        $this->assertEquals($expected, $result);
    }
}
