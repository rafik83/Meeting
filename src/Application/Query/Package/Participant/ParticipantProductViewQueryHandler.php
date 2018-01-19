<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Application\Components\Package\ProductByParticipantGetter;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Package\Product\IncludedParticipantGuesser;
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;

class ParticipantProductViewQueryHandler
{
    /** @var IncludedParticipantGuesser */
    private $includedParticipantGuesser;

    /** @var ProductByParticipantGetter */
    private $productByParticipantGetter;

    /** @var CartManager */
    private $cartManager;

    /**
     * @param IncludedParticipantGuesser $includedParticipantGuesser
     * @param ProductByParticipantGetter $productByParticipantGetter
     * @param CartManager                $cartManager
     */
    public function __construct(
        IncludedParticipantGuesser $includedParticipantGuesser,
        ProductByParticipantGetter $productByParticipantGetter,
        CartManager $cartManager
    ) {
        $this->includedParticipantGuesser = $includedParticipantGuesser;
        $this->productByParticipantGetter = $productByParticipantGetter;
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

        $productParticipants = $this->productByParticipantGetter->getFromCart($this->cartManager->getCart($sheet));

        $productAlreadyBought = [];

        foreach ($productParticipants as $participantId => $productParticipant) {
            if ($productParticipant === null) {
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

            if ($includedQuantity > 0 && isset($productAlreadyBought[$participantProduct->getId()])) {
                $remainingQuantityIncluded = $includedQuantity - count($productAlreadyBought[$participantProduct->getId()]);
            }

            $quantityBought = 0;

            if (isset($productAlreadyBought[$participantProduct->getId()])) {
                $quantityBought = count($productAlreadyBought[$participantProduct->getId()]);
            }

            $participantProductViews[] = new ParticipantProductView(
                $participantProduct->getId(),
                $participantProduct->getTitle($locale),
                $participantProduct->getDescription($locale) ?? '',
                $participantProduct->getUnitPrice(),
                $participantProduct->getCurrency(),
                $participantProduct->getVatMode(),
                $participantProduct->getQuantityMax(),
                $includedQuantity,
                $participantProduct->getQuantityMax() > $quantityBought,
                $remainingQuantityIncluded > 0
            );
        }

        return $participantProductViews;
    }
}
