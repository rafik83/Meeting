<?php

namespace Proximum\Vimeet\Tests\Application\Query\Package\Summary;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQueryHandler;
use Proximum\Vimeet\Application\View\Package\Summary\ProductView;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ProductFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ProductViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $sheet   = SheetFactory::create($event);
        $product = ProductFactory::create($event);
        $locale  = 'fr';
        $product->translate($locale, 'Product1', '', '', '', '');
        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);

        $query = new ProductViewQuery(
            $sheet,
            $product,
            $cart,
            $locale,
            null
        );

        // Expected
        $expectedProductView = new ProductView(
            null,
            'Product1', // no translations
            25,
            1, // cartrow quantity
            25, // total
            $event->getMode(),
            20,
            $event->getCurrency()
        );

        $handler     = new ProductViewQueryHandler();
        $productView = $handler->handle($query);

        $this->assertEquals($productView, $expectedProductView);
    }

    public function testProductNotFoundException()
    {
        $this->expectException(\Exception::class);

        $event   = EventFactory::createEvent();
        $sheet   = SheetFactory::create($event);
        $product = ProductFactory::create($event);
        $cart    = new Cart($sheet, [], []);
        $locale  = 'fr';

        $query = new ProductViewQuery(
            $sheet,
            $product,
            $cart,
            $locale,
            null
        );

        $handler = new ProductViewQueryHandler();
        $handler->handle($query);
    }
}
