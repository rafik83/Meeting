<?php

namespace Proximum\Vimeet\Domain\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Happening\ParticipateToHappeningsByProduct;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ProductsAttributedToParticipantRemoveAllBySheet
{
    /** @var ParticipateToHappeningsByProduct */
    private $participateToHappeningsByProduct;

    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /** @var ProductAttributedToParticipantSetter */
    private $productAttributedToParticipantSetter;

    public function __construct(
        ParticipateToHappeningsByProduct $participateToHappeningsByProduct,
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        ProductAttributedToParticipantSetter $productAttributedToParticipantSetter
    ) {
        $this->participateToHappeningsByProduct = $participateToHappeningsByProduct;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->productAttributedToParticipantSetter = $productAttributedToParticipantSetter;
    }

    public function handle(Sheet $sheet): void
    {
        $sheetParticipants = $sheet->getParticipantsArray();

        $productsAttributedToParticipant = $this->productAttributedToParticipantRepository->findByParticipants(
            $sheetParticipants
        );

        /** @var Product[] $productsAttributed */
        $productsAttributed = [];

        foreach ($productsAttributedToParticipant as $productAttributedToParticipant) {
            $product = $productAttributedToParticipant->getProduct();
            $productsAttributed[$product->getId()] = $product;
        }

        foreach ($productsAttributed as $product) {
            $this->productAttributedToParticipantSetter->attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
                $product,
                $sheetParticipants,
                [] // remove for all participants
            );
        }

        $this->productAttributedToParticipantRepository->removeBatch($productsAttributedToParticipant);

        $this->participateToHappeningsByProduct->handle($sheet);
    }
}
