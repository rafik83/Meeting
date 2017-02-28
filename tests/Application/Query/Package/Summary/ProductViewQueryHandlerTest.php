<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Query\Package\Summary;

use Factory\ProductFactory;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQuery;
use Proximum\Vimeet\Application\Query\Package\Summary\ProductViewQueryHandler;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class ProductViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event   = EventFactory::createEvent();
        $sheet   = SheetFactory::create($event);
        $product = ProductFactory::create($event);
        $cartRow = new CartRow($sheet, $product, 1);
        $cart    = new Cart($sheet, [$cartRow], []);
        $locale  = 'fr';

        $query = new ProductViewQuery(
            $sheet,
            $product,
            $cart,
            $locale,
            null
        );

        // Expected
        $expectedProductView = null;

        $handler = new ProductViewQueryHandler();

        $productView = $handler->handle($query);
    }
}
