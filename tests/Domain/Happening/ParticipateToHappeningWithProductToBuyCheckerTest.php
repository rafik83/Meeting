<?php

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\PackageProductsNeededByHappening;
use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningWithProductToBuyChecker;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ParticipateToHappeningWithProductToBuyCheckerTest extends TestCase
{
    private $package;
    private $sheet;
    private $participant;
    private $toHappening;
    private $productAttributedToParticipantRepository;
    private $participateToHappeningWithProductToBuyChecker;
    private $packageProductsNeededByHappening;
    private $product;

    public function setUp()
    {
        $this->product = $this->prophesize(Product::class);
        $this->package = $this->prophesize(Package::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getPackage()->willReturn($this->package->reveal());

        $this->participant = $this->prophesize(Participant::class);
        $this->participant->getSheet()->willReturn($this->sheet->reveal());

        $this->toHappening = $this->prophesize(Happening::class);

        $this->productAttributedToParticipantRepository = $this->prophesize(
            ProductAttributedToParticipantRepositoryInterface::class
        );

        $this->packageProductsNeededByHappening = $this->prophesize(PackageProductsNeededByHappening::class);

        $this->participateToHappeningWithProductToBuyChecker = new ParticipateToHappeningWithProductToBuyChecker(
            $this->productAttributedToParticipantRepository->reveal(),
            $this->packageProductsNeededByHappening->reveal()
        );
    }

    public function testHappeningHasNotProduct()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(false);
        $this->assertTrue(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }

    public function testPackageIsNotPassable()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->package->isPassable()->shouldBeCalled()->willReturn(false);

        $this->assertTrue(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }

    public function testPackageHasNotAtLeastOneProductNeededByHappening()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->package->isPassable()->shouldBeCalled()->willReturn(true);

        $this
            ->packageProductsNeededByHappening
            ->get($this->package->reveal(), $this->toHappening->reveal())
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->assertTrue(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }

    public function testParticipantHasNotRequiredProduct()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->package->isPassable()->shouldBeCalled()->willReturn(true);

        $this
            ->packageProductsNeededByHappening
            ->get($this->package->reveal(), $this->toHappening->reveal())
            ->shouldBeCalled()
            ->willReturn([$this->product->reveal()])
        ;

        $this
            ->productAttributedToParticipantRepository->participantHasAtLeastOneProduct(
                $this->participant,
                [$this->product->reveal()]
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $this->assertFalse(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }

    public function testParticipantHasRequiredProduct()
    {
        $this->toHappening->hasProducts()->shouldBeCalled()->willReturn(true);
        $this->package->isPassable()->shouldBeCalled()->willReturn(true);

        $this
            ->packageProductsNeededByHappening
            ->get($this->package->reveal(), $this->toHappening->reveal())
            ->shouldBeCalled()
            ->willReturn([$this->product->reveal()])
        ;

        $this
            ->productAttributedToParticipantRepository->participantHasAtLeastOneProduct(
                $this->participant,
                [
                    $this->product->reveal(),
                ]
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->assertTrue(
            $this->participateToHappeningWithProductToBuyChecker->canParticipate(
                $this->participant->reveal(),
                $this->toHappening->reveal()
            )
        );
    }
}
