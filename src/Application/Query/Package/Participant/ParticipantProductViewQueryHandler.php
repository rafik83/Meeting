<?php

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Application\Components\Package\ProductByParticipantCartGetter;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Package\Product\IncludedParticipantGuesser;
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;

class ParticipantProductViewQueryHandler
{
    /** @var IncludedParticipantGuesser */
    private $includedParticipantGuesser;

    /** @var ProductByParticipantCartGetter */
    private $productByParticipantCartGetter;

    /** @var CartManager */
    private $cartManager;

    /**
     * @param IncludedParticipantGuesser     $includedParticipantGuesser
     * @param ProductByParticipantCartGetter $productByParticipantCartGetter
     * @param CartManager                    $cartManager
     */
    public function __construct(
        IncludedParticipantGuesser $includedParticipantGuesser,
        ProductByParticipantCartGetter $productByParticipantCartGetter,
        CartManager $cartManager
    ) {
        $this->includedParticipantGuesser = $includedParticipantGuesser;
        $this->productByParticipantCartGetter = $productByParticipantCartGetter;
        $this->cartManager = $cartManager;
    }

    /**
     * @param ParticipantProductViewQuery $participantProductViewQuery
     *
     * @return ParticipantProductView[]
     */
    public function handle(ParticipantProductViewQuery $participantProductViewQuery): array
    {
        $sheet = $participantProductViewQuery->sheet;
        $locale = $participantProductViewQuery->locale;

        if (!$sheet->getPackage()->isPassable()) {
            return [];
        }

        $participantProductViews = [];

        $includedParticipantViews = $this->includedParticipantGuesser->getIncludedParticipantViews($sheet);

        $productParticipants = $this->productByParticipantCartGetter->getFromCart($this->cartManager->getCart($sheet));

        $productAlreadyBought = [];

        foreach ($productParticipants as $participantId => $productParticipant) {
            if (null === $productParticipant) {
                continue;
            }

            $productAlreadyBought[$productParticipant->getId()][] = $participantId;
        }

        foreach ($sheet->getPackage()->getParticipants() as $participantProduct) {
            $includedQuantity = 0;
            $remainingQuantityIncluded = 0;

            if (isset($includedParticipantViews[$participantProduct->getId()])) {
                $includedParticipantView = $includedParticipantViews[$participantProduct->getId()];

                if ($includedParticipantView instanceof IncludedParticipantView) {
                    $includedQuantity = $includedParticipantView->totalQuantity;
                }
            }

            if ($includedQuantity > 0) {
                $quantityIncludedBought = isset($productAlreadyBought[$participantProduct->getId()])
                    ? count($productAlreadyBought[$participantProduct->getId()])
                    : 0
                ;

                $remainingQuantityIncluded = $includedQuantity - $quantityIncludedBought;
            }

            $quantityBought = 0;

            if (isset($productAlreadyBought[$participantProduct->getId()])) {
                $quantityBought = count($productAlreadyBought[$participantProduct->getId()]);
            }

            $participantProductViews[] = new ParticipantProductView(
                $participantProduct->getId(),
                null === $locale ? '' : $participantProduct->getTitle($locale),
                null === $locale ? '' : $participantProduct->getDescription($locale) ?? '',
                $participantProduct->getUnitPrice(),
                $participantProduct->getCurrency(),
                $participantProduct->getVatMode(),
                $participantProduct->getQuantityMax(),
                $includedQuantity,
                $participantProduct->getQuantityMax() > $quantityBought,
                $remainingQuantityIncluded < 0 ? 0 : $remainingQuantityIncluded,
                $remainingQuantityIncluded > 0
            );
        }

        return $participantProductViews;
    }
}
