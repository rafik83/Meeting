<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Participant;

use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Package\Product\IncludedParticipantGuesser;

class ParticipantProductViewQueryHandler
{
    /** @var IncludedParticipantGuesser */
    private $includedParticipantGuesser;

    /**
     * @param IncludedParticipantGuesser $includedParticipantGuesser
     */
    public function __construct(IncludedParticipantGuesser $includedParticipantGuesser)
    {
        $this->includedParticipantGuesser = $includedParticipantGuesser;
    }

    /**
     * @param ParticipantProductViewQuery $participantProductViewQuery
     *
     * @return null|ParticipantProductView
     */
    public function handle(ParticipantProductViewQuery $participantProductViewQuery)
    {
        $sheet  = $participantProductViewQuery->sheet;
        $locale = $participantProductViewQuery->locale;

        if (!$sheet->getPackage()->isPassable()) {
            return null;
        }

        $includedParticipantView = $this->includedParticipantGuesser->getIncludedParticipantView($sheet);

        if ($includedParticipantView->remainingQuantity > 0) {
            $isIncluded         = true;
            $participantProduct = $includedParticipantView->product;
        } else {
            $isIncluded         = false;
            $participantProduct = $sheet->getPackage()->getParticipant();
        }

        return new ParticipantProductView(
            $participantProduct->getTitle($locale),
            $participantProduct->getUnitPrice(),
            $participantProduct->getCurrency(),
            $participantProduct->getVatMode(),
            $isIncluded
        );
    }
}
