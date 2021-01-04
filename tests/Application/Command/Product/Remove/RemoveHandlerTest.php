<?php

namespace Proximum\Vimeet\Tests\Application\Command\Product\Remove;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Command\Product\Remove\Remove;
use Proximum\Vimeet\Application\Command\Product\Remove\RemoveHandler;
use Proximum\Vimeet\Application\Exception\Product\CanNotBeRemovedException;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Product\RemoveAuthorizationChecker;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class RemoveHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $product;

    /** @var ObjectProphecy */
    private $productRepository;

    /** @var ObjectProphecy */
    private $removeAuthorizationChecker;

    public function setUp()
    {
        $this->product = $this->prophesize(Product::class);
        $this->productRepository = $this->prophesize(ProductRepositoryInterface::class);
        $this->removeAuthorizationChecker = $this->prophesize(RemoveAuthorizationChecker::class);
    }

    public function testHandleException()
    {
        $this->expectException(CanNotBeRemovedException::class);

        $this->removeAuthorizationChecker->canBeRemoved($this->product->reveal())->shouldBeCalled()->willReturn(false);

        // Handler
        $handler = new RemoveHandler(
            $this->productRepository->reveal(),
            $this->removeAuthorizationChecker->reveal()
        );
        $handler->handle(new Remove($this->product->reveal()));
    }

    public function testHandle()
    {
        $this->removeAuthorizationChecker->canBeRemoved($this->product->reveal())->shouldBeCalled()->willReturn(true);
        $this->productRepository->remove($this->product->reveal())->shouldBeCalled();

        // Handler
        $handler = new RemoveHandler(
            $this->productRepository->reveal(),
            $this->removeAuthorizationChecker->reveal()
        );
        $handler->handle(new Remove($this->product->reveal()));
    }
}
