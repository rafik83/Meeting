<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Order\Export\ProductViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\ProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\ProductView;
use Proximum\Vimeet\Domain\Model\Product;

class ProductViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $adminLocale = 'fr';
        $locale      = 'en';
        $translator  = $this->prophesize(TranslatorInterface::class);
        $product     = $this->prophesize(Product::class);
        $product->getTitle('en')->willReturn('product title');
        $product->getId()->willReturn(2);

        $translator->trans('order.column.product.unitPrice', ['%productTitle%' => 'product title'], 'export', $adminLocale)->shouldBeCalled()->willReturn('product title unitPrice');
        $translator->trans('order.column.product.quantity', ['%productTitle%' => 'product title'], 'export', $adminLocale)->shouldBeCalled()->willReturn('product title quantity');
        $translator->trans('order.column.product.total', ['%productTitle%' => 'product title'], 'export', $adminLocale)->shouldBeCalled()->willReturn('product title total');

        $handler = new ProductViewQueryHandler($translator->reveal());
        $result = $handler->handle(new ProductViewQuery($product->reveal(), $locale, $adminLocale));

        $expected = new ProductView(2, 'product title', 'product title unitPrice', 'product title quantity', 'product title total');
        $this->assertEquals($expected, $result);
    }
}
