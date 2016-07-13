<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\ParticipantGroupView;

class ParticipantGroupViewQueryHandler
{
    /**
     * @var ProductViewQueryHandler
     */
    private $productViewQueryHandler;

    /**
     * @param ProductViewQueryHandler $productViewQueryHandler
     */
    public function __construct(ProductViewQueryHandler $productViewQueryHandler)
    {
        $this->productViewQueryHandler = $productViewQueryHandler;
    }

    /**
     * @param ParticipantGroupViewQuery $participantGroupViewQuery
     *
     * @return ParticipantGroupView
     * @throws \Exception
     */
    public function handle(ParticipantGroupViewQuery $participantGroupViewQuery)
    {
        $cart    = $participantGroupViewQuery->cart;
        $package = $participantGroupViewQuery->sheet->getPackage();

        if (!$package->isParticipantAndPlanningEnabled()) {
            throw new \Exception('Participant is not enabled');
        }

        $participantRow  = $cart->getParticipantRow();
        $participantView = null;

        if (null !== $participantRow) {
            $participantView = $this->productViewQueryHandler->handle(new ProductViewQuery(
                $participantGroupViewQuery->sheet,
                $participantRow->getProduct(),
                $cart,
                $participantGroupViewQuery->locale,
                $participantGroupViewQuery->planGroupView
            ));
        }

        return new ParticipantGroupView(
            $package->getParticipant()->getTitle($participantGroupViewQuery->locale),
            [$participantView],
            null !== $participantView ? $participantView->total : 0
        );
    }
}
