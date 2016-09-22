<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Application\View\Package\ParticipantsView;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Order\Merger;

class ParticipantsViewQueryHandler
{
    /**
     * @var ParticipantViewQueryHandler
     */
    private $participantViewQueryHandler;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @var Merger
     */
    private $orderMerger;

    /**
     * @param ParticipantViewQueryHandler $participantViewQueryHandler
     * @param CartManager                 $cartManager
     * @param Merger                      $orderMerger
     */
    public function __construct(
        ParticipantViewQueryHandler $participantViewQueryHandler,
        CartManager $cartManager,
        Merger $orderMerger
    ) {
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->cartManager                 = $cartManager;
        $this->orderMerger                 = $orderMerger;
    }

    /**
     * @param ParticipantsViewQuery $participantsViewQuery
     * @return ParticipantsView
     */
    public function handle(ParticipantsViewQuery $participantsViewQuery)
    {
        $cart               = $this->cartManager->getCart($participantsViewQuery->sheet);
        $locale             = $participantsViewQuery->locale;
        $participantProduct = $participantsViewQuery->sheet->getPackage()->getParticipant();

        if ($participantsViewQuery->sheet->hasNotCancelledOrders()) {
            $orderMerged  = $this->orderMerger->merge($participantsViewQuery->sheet->getNotCancelledOrders());
            $selectedPlan = $orderMerged->getPlan();
        } else {
            $selectedPlan = $cart->getPlanRow()->getProduct();
        }

        $numberIncluded = 0;

        if ($selectedPlan) {
            $participantProductIncluded = $selectedPlan->getIncludedParticipantProduct();

            if ($participantProductIncluded) {
                $numberIncluded = $participantProductIncluded->getQuantity();
            }
        }

        $participantView = [];

        foreach ($participantsViewQuery->sheet->getParticipants() as $participant) {
            $participantView[] = $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery(
                    $participantProduct,
                    $participant,
                    $locale,
                    $numberIncluded > 0
                )
            );

            $numberIncluded--;
        }

        $participantsView = new ParticipantsView(
            $participantProduct->getTitle($locale),
            $participantProduct->getDescription($locale),
            $participantView
        );

        return $participantsView;
    }
}
