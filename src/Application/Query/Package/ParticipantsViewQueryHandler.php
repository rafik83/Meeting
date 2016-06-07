<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package;

use Proximum\Vimeet\Application\View\Package\ParticipantsView;
use Proximum\Vimeet\Domain\Model\Product\ProductIncluded;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class ParticipantsViewQueryHandler
{
    /**
     * @var ParticipantViewQueryHandler
     */
    private $participantViewQueryHandler;

    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @param ParticipantViewQueryHandler $participantViewQueryHandler
     * @param CartRowRepositoryInterface  $cartRowRepository
     */
    public function __construct(
        ParticipantViewQueryHandler $participantViewQueryHandler,
        CartRowRepositoryInterface $cartRowRepository
    ) {
        $this->participantViewQueryHandler = $participantViewQueryHandler;
        $this->cartRowRepository           = $cartRowRepository;
    }

    /**
     * @param ParticipantsViewQuery $participantsViewQuery
     * @return ParticipantsView
     */
    public function handle(ParticipantsViewQuery $participantsViewQuery)
    {
        $locale             = $participantsViewQuery->locale;
        $participantProduct = $participantsViewQuery->sheet->getPackage()->getParticipant();
        $planBought         = $this->cartRowRepository->findCartRowPlanBySheet($participantsViewQuery->sheet);
        $numberIncluded     = 0;

        if (null !== $planBought) {
            $participantProductIncluded = array_filter($planBought->getProduct()->getIncludedProducts(), function (ProductIncluded $productIncluded) {
                return $productIncluded->getProduct()->isParticipant();
            });

            $participantProductIncluded = reset($participantProductIncluded);
            $numberIncluded = $participantProductIncluded->getQuantity();
        }

        $participantView = [];

        foreach ($participantsViewQuery->sheet->getParticipants() as $participant) {
            $participantView[] = $this->participantViewQueryHandler->handle(
                new ParticipantViewQuery(
                    $participantProduct,
                    $participant,
                    $locale,
                    $numberIncluded > 0 ? true : false
                )
            );

            $numberIncluded--;
        }

        $participantsView = new ParticipantsView(
            $participantProduct->getTitle($locale),
            $participantProduct->getDescription($locale)
        );

        $participantsView->participants = $participantsView;

        return $participantsView;
    }
}
