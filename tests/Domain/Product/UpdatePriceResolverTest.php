<?php

namespace Proximum\Vimeet\Tests\Domain\Product;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\Row;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Order\RowRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdatePriceResolverTest extends TestCase
{
    public function testResolveTrue()
    {
        $event   = EventFactory::createEvent();
        $product = new Product(
            $event,
            'type',
            'name',
            'image',
            10,
            20,
            10,
            10,
            10,
            true
        );

        //Mock
        $cartRowRepository  = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->findByProduct($product)->shouldBeCalled()->willReturn([]);
        $orderRowRepository = $this->prophesize(RowRepositoryInterface::class);
        $orderRowRepository->findByProduct($product)->shouldBeCalled()->willReturn([]);

        // Resolve
        $updatePriceResolver = new UpdatePriceResolver(
            $cartRowRepository->reveal(),
            $orderRowRepository->reveal()
        );
        $this->assertTrue($updatePriceResolver->resolve($product));
    }

    public function testResolveFalseWithCartRow()
    {
        $event   = EventFactory::createEvent();
        $user    = new User('', '', '', '');
        $type    = new Type($event);
        $sheet   = new Sheet($event, $type, [], $user, new \DateTime());
        $product = new Product(
            $event,
            'type',
            'name',
            'image',
            10,
            20,
            10,
            10,
            10,
            true
        );

        //Mock
        $cartRowRepository  = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->findByProduct($product)->shouldBeCalled()->willReturn([new CartRow($sheet, $product, 2)]);
        $orderRowRepository = $this->prophesize(RowRepositoryInterface::class);
        $orderRowRepository->findByProduct($product)->shouldBeCalled()->willReturn([]);

        // Resolve
        $updatePriceResolver = new UpdatePriceResolver(
            $cartRowRepository->reveal(),
            $orderRowRepository->reveal()
        );
        $this->assertFalse($updatePriceResolver->resolve($product));
    }

    public function testResolveFalseWithOrderRow()
    {
        $date    = new \DateTime();
        $event   = EventFactory::createEvent();
        $user    = new User('', '', '', '');
        $type    = new Type($event);
        $sheet   = new Sheet($event, $type, [], $user, $date);
        $product = new Product(
            $event,
            'type',
            'name',
            'image',
            10,
            20,
            10,
            10,
            10,
            true
        );
        $order = new Order(
            $sheet,
            '',
            $date
        );

        //Mock
        $cartRowRepository  = $this->prophesize(CartRowRepositoryInterface::class);
        $cartRowRepository->findByProduct($product)->shouldBeCalled()->willReturn([]);
        $orderRowRepository = $this->prophesize(RowRepositoryInterface::class);
        $orderRowRepository
            ->findByProduct($product)
            ->shouldBeCalled()
            ->willReturn([new Row($order, 2, 20, $product, 1, 'label', 100)])
        ;

        // Resolve
        $updatePriceResolver = new UpdatePriceResolver(
            $cartRowRepository->reveal(),
            $orderRowRepository->reveal()
        );
        $this->assertFalse($updatePriceResolver->resolve($product));
    }
}
