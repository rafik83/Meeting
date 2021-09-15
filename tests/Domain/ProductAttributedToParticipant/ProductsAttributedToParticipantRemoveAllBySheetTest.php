<?php

namespace Proximum\Vimeet\Tests\Domain\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductAttributedToParticipantSetter;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ProductsAttributedToParticipantRemoveAllBySheet;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ProductsAttributedToParticipantRemoveAllBySheetTest extends TestCase
{
    public function testHandle()
    {
        $product = $this->prophesize(Product::class);
        $product->getId()->willReturn(1);

        $productAttributedToParticipant1 = $this->prophesize(ProductAttributedToParticipant::class);
        $productAttributedToParticipant1->getProduct()->willReturn($product->reveal());

        $productAttributedToParticipant2 = $this->prophesize(ProductAttributedToParticipant::class);
        $productAttributedToParticipant2->getProduct()->willReturn($product->reveal());

        $participant = $this->prophesize(Participant::class);

        $sheet = $this->prophesize(Sheet::class);
        $sheet->getParticipantsArray()->willReturn([$participant->reveal()]);

        $productAttributedToParticipantRepository = $this->prophesize(
            ProductAttributedToParticipantRepositoryInterface::class
        );
        $productAttributedToParticipantRepository
            ->findByParticipants([$participant->reveal()])
            ->willReturn([$productAttributedToParticipant1->reveal(), $productAttributedToParticipant2->reveal()])
        ;
        $productAttributedToParticipantRepository
            ->removeBatch(
                [
                    $productAttributedToParticipant1->reveal(),
                    $productAttributedToParticipant2->reveal(),
                ]
            )
            ->shouldBeCalled()
        ;

        $productAttributedToParticipantSetter = $this->prophesize(ProductAttributedToParticipantSetter::class);
        $productAttributedToParticipantSetter
            ->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $product->reveal(),
                [$participant->reveal()],
                []
            )
            ->shouldBeCalled()
        ;

        $participateToHappeningsByProduct = $this->prophesize(ParticipateToHappeningsByProduct::class);
        $participateToHappeningsByProduct->handle($sheet->reveal())->shouldBeCalled();

        $productsAttributedToParticipantRemoveAllBySheet = new ProductsAttributedToParticipantRemoveAllBySheet(
            $participateToHappeningsByProduct->reveal(),
            $productAttributedToParticipantRepository->reveal(),
            $productAttributedToParticipantSetter->reveal()
        );
        $productsAttributedToParticipantRemoveAllBySheet->handle($sheet->reveal());
    }
}
