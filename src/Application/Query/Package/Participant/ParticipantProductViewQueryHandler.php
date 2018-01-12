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
use Proximum\Vimeet\Domain\View\Package\Product\IncludedParticipantView;

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

        foreach ($sheet->getPackage()->getParticipants() as $participantProduct) {
            $includedQuantity = 0;

            if (isset($includedParticipantViews[$participantProduct->getId()])) {
                $includedParticipantView = $includedParticipantViews[$participantProduct->getId()];

                if ($includedParticipantView instanceof IncludedParticipantView) {
                    $includedQuantity = $includedParticipantView->totalQuantity;
                }
            }

            $participantProductViews[] = new ParticipantProductView(
                $participantProduct->getId(),
                $participantProduct->getTitle($locale),
                $participantProduct->getDescription($locale) ?? '',
                $participantProduct->getUnitPrice(),
                $participantProduct->getCurrency(),
                $participantProduct->getVatMode(),
                (int) $participantProduct->getQuantityMax(),
                (int) $includedQuantity
            );
        }

        return $participantProductViews;
    }
}
