<?php

namespace Proximum\Vimeet\Tests\Domain\Cart;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Cart\Cart;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class CartTest extends TestCase
{
    public function testAdd()
    {
        $datetime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('john.doe@example.net', '_salt_', '_password_', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $datetime);

        $optionA = Product::createOption($event, 'Option A', 'optionA.jpg', 100, 20, 3, 3, 3, true);
        $optionB = Product::createOption($event, 'Option B', 'optionB.jpg', 100, 20, 3, 3, 3, true);

        $cart = new Cart($sheet, [], []);
        $cart->setProduct($optionA, 1);
        $this->assertCount(1, $cart->getRows());
        $this->assertEquals(1, $cart->getRow($optionA)->getQuantity());

        $cart->setProduct($optionA, 2);
        $this->assertCount(1, $cart->getRows());
        $this->assertEquals(2, $cart->getRow($optionA)->getQuantity());

        $cart->setProduct($optionB, 1);
        $this->assertCount(2, $cart->getRows());
        $this->assertEquals(2, $cart->getRow($optionA)->getQuantity());
        $this->assertEquals(1, $cart->getRow($optionB)->getQuantity());
    }
}
