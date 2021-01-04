<?php

namespace Proximum\Vimeet\Tests\Domain\ProductAttributedToParticipant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Exception\Product\ProductIsNotAttributableException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\ProductAttributedToParticipant\ParticipantWithAttributedProductGetter;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipant\ParticipantWithAttributedProductRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantWithAttributedProductGetterTest extends TestCase
{
    public function testGetParticipantsCompleteNameByAttributedProduct()
    {
        $attributableProduct = $this->prophesize(Product::class);
        $attributableProduct->isAttributable()->willReturn(true);

        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);
        $participant3 = $this->prophesize(Participant::class);

        $participantWithAttributedProductRepository = $this->prophesize(
            ParticipantWithAttributedProductRepositoryInterface::class
        );
        $participantWithAttributedProductRepository
            ->getParticipantsWithAttributedProductForProduct(
                [
                    $participant1->reveal(),
                    $participant2->reveal(),
                    $participant3->reveal(),
                ],
                $attributableProduct
            )
            ->shouldBeCalled()
            ->willReturn(
                [
                    $participant1->reveal(),
                    $participant3->reveal(),
                ]
            )
        ;

        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser
            ->guessParticipantCompleteName($participant1->reveal())
            ->shouldBeCalled()
            ->willReturn('Bruce WILLIS')
        ;
        $participantInfoGuesser
            ->guessParticipantCompleteName($participant2->reveal())
            ->shouldNotBeCalled()
        ;
        $participantInfoGuesser
            ->guessParticipantCompleteName($participant3->reveal())
            ->shouldBeCalled()
            ->willReturn('John TRAVOLTA')
        ;

        $participantWithAttributedProductGetter = new ParticipantWithAttributedProductGetter(
            $participantWithAttributedProductRepository->reveal(),
            $participantInfoGuesser->reveal()
        );
        $results = $participantWithAttributedProductGetter->getParticipantsCompleteNameByAttributedProduct(
            [
                $participant1->reveal(),
                $participant2->reveal(),
                $participant3->reveal(),
            ],
            $attributableProduct->reveal()
        );

        $this->assertEquals(
            [
                'Bruce WILLIS',
                'John TRAVOLTA',
            ],
            $results
        );
    }

    public function testProductIsNotAttributableException()
    {
        $this->expectException(ProductIsNotAttributableException::class);

        $notAttributableProduct = $this->prophesize(Product::class);
        $notAttributableProduct->isAttributable()->willReturn(false);

        $participant = $this->prophesize(Participant::class);

        $participantWithAttributedProductRepository = $this->prophesize(
            ParticipantWithAttributedProductRepositoryInterface::class
        );
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);

        $participantWithAttributedProductGetter = new ParticipantWithAttributedProductGetter(
            $participantWithAttributedProductRepository->reveal(),
            $participantInfoGuesser->reveal()
        );
        $participantWithAttributedProductGetter->getParticipantsCompleteNameByAttributedProduct(
            [$participant->reveal()],
            $notAttributableProduct->reveal()
        );
    }
}
