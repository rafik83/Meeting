<?php

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Happening\HappeningsWithProductsBySheetPackageGetter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningsWithProductsBySheetPackageGetterTest extends TestCase
{
    /** @var ObjectProphecy */
    private $happeningRepository;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $package;

    /** @var ObjectProphecy */
    private $type;

    /** @var ObjectProphecy */
    private $happening1;
    /** @var ObjectProphecy */
    private $happening2;
    /** @var ObjectProphecy */
    private $happening3;
    /** @var ObjectProphecy */
    private $happening4;
    /** @var ObjectProphecy */
    private $happening5;

    /** @var ObjectProphecy */
    private $product1;
    /** @var ObjectProphecy */
    private $product2;
    /** @var ObjectProphecy */
    private $product3;
    /** @var ObjectProphecy */
    private $product4;
    /** @var ObjectProphecy */
    private $product5;

    public function setUp()
    {
        $this->happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->event = $this->prophesize(Event::class);
        $this->package = $this->prophesize(Package::class);
        $this->type = $this->prophesize(Type::class);

        $this->sheet->getEvent()->willReturn($this->event->reveal());
        $this->sheet->getType()->willReturn($this->type->reveal());
        $this->sheet->getPackage()->willReturn($this->package->reveal());

        $this->happening1 = $this->prophesize(Happening::class);
        $this->happening2 = $this->prophesize(Happening::class);
        $this->happening3 = $this->prophesize(Happening::class);
        $this->happening4 = $this->prophesize(Happening::class);
        $this->happening5 = $this->prophesize(Happening::class);

        $this->happening1->getId()->willReturn(111);
        $this->happening2->getId()->willReturn(112);
        $this->happening3->getId()->willReturn(113);
        $this->happening4->getId()->willReturn(114);
        $this->happening5->getId()->willReturn(115);

        $this->product1 = $this->prophesize(Product::class);
        $this->product2 = $this->prophesize(Product::class);
        $this->product3 = $this->prophesize(Product::class);
        $this->product4 = $this->prophesize(Product::class);
        $this->product5 = $this->prophesize(Product::class);

        $this->product1->getId()->willReturn(211);
        $this->product2->getId()->willReturn(212);
        $this->product3->getId()->willReturn(213);
        $this->product4->getId()->willReturn(214);
        $this->product5->getId()->willReturn(215);

        $this->happening1->getProducts()->willReturn([$this->product1->reveal(), $this->product3->reveal()]);
        $this->happening2->getProducts()->willReturn([$this->product2->reveal()]);
        $this->happening3->getProducts()->willReturn([$this->product3->reveal()]);
        $this->happening4->getProducts()->willReturn([$this->product5->reveal()]);
        $this->happening5->getProducts()->willReturn([$this->product5->reveal(), $this->product4->reveal()]);
    }

    public function testNoHappeningWithProduct(): void
    {
        $this->happeningRepository
            ->findWithProductsAndType($this->event->reveal(), $this->type->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $happeningsWithProductsBySheetPackageGetter = new HappeningsWithProductsBySheetPackageGetter(
            $this->happeningRepository->reveal()
        );

        $result = $happeningsWithProductsBySheetPackageGetter->get($this->sheet->reveal());

        $this->assertEquals([], $result);
    }

    public function testGet(): void
    {

        $this->package
            ->getAttributableOptions()
            ->shouldBeCalled()
            ->willReturn([
                213 => $this->product3->reveal(),
                212 => $this->product2->reveal(),
            ])
        ;
        $this->happeningRepository
            ->findWithProductsAndType($this->event->reveal(), $this->type->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $this->happening1->reveal(),
                $this->happening2->reveal(),
                $this->happening3->reveal(),
                $this->happening4->reveal(),
                $this->happening5->reveal(),
            ])
        ;

        $happeningsWithProductsBySheetPackageGetter = new HappeningsWithProductsBySheetPackageGetter(
            $this->happeningRepository->reveal()
        );

        $result = $happeningsWithProductsBySheetPackageGetter->get($this->sheet->reveal());

        $expected = [
            111 => $this->happening1->reveal(),
            112 => $this->happening2->reveal(),
            113 => $this->happening3->reveal(),
        ];
        $this->assertEquals($expected, $result);
    }
}
