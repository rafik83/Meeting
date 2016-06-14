<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Planning;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class QuantityMaxGuesser
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository)
    {
        $this->cartRowRepository = $cartRowRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getMaxPlanning(Sheet $sheet)
    {
        if (null === $sheet->getPackage()->getPlanning()) {
            return 0;
        }

        $planningQuantityMax = $sheet->getPackage()->getPlanning()->getQuantityMax();

        $selectedPlan = $this->cartRowRepository->findCartRowPlanBySheet($sheet);

        if (null === $selectedPlan) {
            return $planningQuantityMax;
        }

        $includedParticipantNumber  = 0;
        $includedParticipantProduct = $selectedPlan->getProduct()->getIncludedParticipantProduct();

        if ($includedParticipantProduct) {
            $includedParticipantNumber = $includedParticipantProduct->getQuantity();
        }

        return min(
            $sheet->getParticipants()->count() - $includedParticipantNumber,
            $planningQuantityMax
        );
    }
}
