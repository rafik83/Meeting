<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\CustomRowBoughtViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\CustomRowBoughtView;
use Proximum\Vimeet\Domain\Model\Order\Row;

class CustomRowBoughtViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $adminLocale = 'fr';
        $row         = $this->prophesize(Row::class);
        $row->getId()->willReturn(1234);
        $row->getLabel()->willReturn('Salut');
        $row->getPrice()->willReturn(12);
        $row->getQuantity()->willReturn(2);

        $query = new CustomRowBoughtViewQuery($row->reveal(), $adminLocale);
        $handler = new CustomRowBoughtViewQueryHandler();
        $result = $handler->handle($query);

        $expected = new CustomRowBoughtView(1234, 'Salut', 12, 2, 24);
        $this->assertEquals($expected, $result);
    }
}
