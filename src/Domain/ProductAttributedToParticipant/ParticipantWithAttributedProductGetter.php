<?php

namespace Proximum\Vimeet\Domain\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Exception\Product\ProductIsNotAttributableException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipant\ParticipantWithAttributedProductRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantWithAttributedProductGetter
{
    /** @var ParticipantWithAttributedProductRepositoryInterface */
    private $participantWithAttributedProductRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    public function __construct(
        ParticipantWithAttributedProductRepositoryInterface $participantWithAttributedProductRepository,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->participantWithAttributedProductRepository = $participantWithAttributedProductRepository;
    }

    /**
     * @param Participant[] $participants
     * @param Product       $product
     *
     * @return string[]
     */
    public function getParticipantsCompleteNameByAttributedProduct(array $participants, Product $product): array
    {
        if (!$product->isAttributable()) {
            throw new ProductIsNotAttributableException('Given product is not attributable');
        }

        $participantsWithAttributedProduct = $this->participantWithAttributedProductRepository
            ->getParticipantsWithAttributedProductForProduct(
                $participants,
                $product
            )
        ;

        $participantsCompleteNameWithAttributedProduct = [];

        foreach ($participantsWithAttributedProduct as $participantWithAttributedProduct) {
            $participantsCompleteNameWithAttributedProduct[] = $this->participantInfoGuesser->guessParticipantCompleteName(
                $participantWithAttributedProduct
            );
        }

        return $participantsCompleteNameWithAttributedProduct;
    }
}
