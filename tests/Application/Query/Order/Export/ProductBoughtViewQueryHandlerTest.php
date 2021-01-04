<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Order\Export\ProductBoughtViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\ProductBoughtViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\ProductBoughtView;
use Proximum\Vimeet\Domain\Model\Order\Row;

class ProductBoughtViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $row = $this->prophesize(Row::class);
        $row->getProductId()->willReturn(23);
        $row->getPrice()->willReturn(12);
        $row->getQuantity()->willReturn(2);

        $query = new ProductBoughtViewQuery($row->reveal());
        $handler = new ProductBoughtViewQueryHandler();
        $result = $handler->handle($query);

        $expected = new ProductBoughtView(23, 12, 2, 24);
        $this->assertEquals($expected, $result);
    }
}
