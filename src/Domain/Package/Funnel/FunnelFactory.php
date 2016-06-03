<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Funnel;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class FunnelFactory
{
    /**
     * @var
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
     * Create a funnel with its steps
     * @param Sheet  $sheet
     * @param string $locale
     *
     * @return Funnel
     */
    public function create(Sheet $sheet, $locale)
    {
        $package = $sheet->getPackage();
        $funnel  = new Funnel();

        if ($package->isPlansEnabled()) {
            $step = new Step($this->getNextIndex($funnel), $package->getPlansLabel($locale), STEP::TYPE_PLAN);
            $funnel->addStep($step);

            $cartRow = $this->cartRowRepository->findCartRowPlanBySheet($sheet);

            if (null !== $cartRow) {
                $step->completed = true;
            }
        }

        if ($package->isParticipantAndPlanningEnabled()) {
            $step = new Step($this->getNextIndex($funnel), $package->getParticipantAndPlanningLabel($locale), STEP::TYPE_PARTICIPANT_PLANNING);
            $funnel->addStep($step);
        }

        if ($package->isOptionsEnabled()) {
            $step = new Step($this->getNextIndex($funnel), $package->getOptionsLabel($locale), STEP::TYPE_OPTIONS);
            $funnel->addStep($step);
        }

        return $funnel;
    }

    /**
     * Get the next available index
     * @param Funnel $funnel
     *
     * @return int
     */
    private function getNextIndex(Funnel $funnel)
    {
        return count($funnel->getSteps()) + 1;
    }
}
