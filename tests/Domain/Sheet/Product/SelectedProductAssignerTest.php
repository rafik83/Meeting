<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet\Product;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Product\SelectedProductAssigner;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;

class SelectedProductAssignerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $merger;

    /** @var ObjectProphecy */
    private $templateDataFactory;

    /** @var ObjectProphecy */
    private $templateData;

    /** @var ObjectProphecy */
    private $order;

    /** @var ObjectProphecy */
    private $product2, $product3, $product4, $product5;

    public function setUp()
    {
        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->templateData = $this->prophesize(TemplateData::class);
        $this->merger = $this->prophesize(Merger::class);
        $this->order = $this->prophesize(Order::class);

        $this->product2 = $this->prophesize(Product::class);
        $this->product3 = $this->prophesize(Product::class);
        $this->product4 = $this->prophesize(Product::class);
        $this->product5 = $this->prophesize(Product::class);

        $this->product2->getId()->shouldBeCalled()->willReturn(12);
        $this->product3->getId()->shouldBeCalled()->willReturn(13);
        $this->product4->getId()->shouldBeCalled()->willReturn(14);
        $this->product5->getId()->shouldBeCalled()->willReturn(15);
    }

    public function testHandle(): void
    {
        $this->templateDataFactory->createFromSheet($this->sheet, null)
            ->shouldBeCalled()
            ->willReturn($this->templateData->reveal())
        ;

        $this->sheet->getNotCancelledOrders()->shouldBeCalled()->willReturn([$this->order->reveal()]);
        $this->merger->merge([$this->order->reveal()])->shouldBeCalled()->willReturn($this->order->reveal());

        $row1 = $this->prophesize(Order\Row::class);
        $row2 = $this->prophesize(Order\Row::class);
        $row3 = $this->prophesize(Order\Row::class);
        $row4 = $this->prophesize(Order\Row::class);
        $row5 = $this->prophesize(Order\Row::class);
        $row6 = $this->prophesize(Order\Row::class);

        $row1->getType()->willReturn(Product::TYPE_PLAN);
        $row2->getType()->willReturn(Product::TYPE_OPTION);
        $row3->getType()->willReturn(Product::TYPE_OPTION);
        $row4->getType()->willReturn(Product::TYPE_OPTION);
        $row5->getType()->willReturn(Product::TYPE_OPTION);
        $row6->getType()->willReturn(Product::TYPE_OPTION);

        $row2->getProduct()->willReturn($this->product2->reveal());
        $row3->getProduct()->willReturn($this->product3->reveal());
        $row4->getProduct()->willReturn($this->product4->reveal());
        $row5->getProduct()->willReturn($this->product5->reveal());
        $row6->getProduct()->willReturn(null);

        $this->order->getRows()->shouldBeCalled()->willReturn([
            $row1->reveal(),
            $row2->reveal(),
            $row3->reveal(),
            $row4->reveal(),
            $row5->reveal(),
            $row6->reveal(),
        ]);

        $object1 = $this->prophesize(TemplateObject::class);
        $object2 = $this->prophesize(TemplateObject::class);
        $object3 = $this->prophesize(TemplateObject::class);
        $object4 = $this->prophesize(TemplateObject::class);
        $object5 = $this->prophesize(TemplateObject::class);
        $object6 = $this->prophesize(TemplateObject::class);

        $object1->isBuyable()->shouldBeCalled()->willReturn(false);
        $object2->isBuyable()->shouldBeCalled()->willReturn(true);
        $object3->isBuyable()->shouldBeCalled()->willReturn(true);
        $object4->isBuyable()->shouldBeCalled()->willReturn(true);
        $object5->isBuyable()->shouldBeCalled()->willReturn(true);
        $object6->isBuyable()->shouldBeCalled()->willReturn(true);

        $object2->getSelectedProduct()->shouldBeCalled()->willReturn(12);
        $object3->getSelectedProduct()->shouldBeCalled()->willReturn(18);
        $object4->getSelectedProduct()->shouldBeCalled()->willReturn(null);
        $object5->getSelectedProduct()->shouldBeCalled()->willReturn(null);
        $object6->getSelectedProduct()->shouldBeCalled()->willReturn(13);

        $object3->getProducts()->shouldBeCalled()->willReturn([18, 19]);
        $object4->getProducts()->shouldBeCalled()->willReturn([14, 15]);
        $object5->getProducts()->shouldBeCalled()->willReturn([19, 20]);

        $object2->setSelectedProduct(12)->shouldNotBeCalled();
        $object6->setSelectedProduct(13)->shouldNotBeCalled();
        $object3->setSelectedProduct(null)->shouldBeCalled();
        $object4->setSelectedProductId(14)->shouldBeCalled();
        $object5->setSelectedProduct(null)->shouldBeCalled();

        $this->templateData->getObjects()->shouldBeCalled()->willReturn([
            $object1->reveal(),
            $object2->reveal(),
            $object3->reveal(),
            $object4->reveal(),
            $object5->reveal(),
            $object6->reveal(),
        ]);

        $this->templateData->getData()->shouldBeCalled()->willReturn(['data']);
        $this->sheet->setData(['data'])->shouldBeCalled();
        $this->sheetRepository->set($this->sheet->reveal())->shouldBeCalled();

        $selectProductAssigner = new SelectedProductAssigner(
            $this->templateDataFactory->reveal(),
            $this->merger->reveal(),
            $this->sheetRepository->reveal()
        );

        $selectProductAssigner->handle($this->sheet->reveal());
    }
}
