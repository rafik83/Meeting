<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Service;

use Proximum\Vimeet\Domain\Model\Sheet;

class ParticipantManager
{
    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getBoughtParticipant(Sheet $sheet)
    {
        $typePackageTemplate = $sheet->getType()->getPackageTemplate();
        $packageData         = $sheet->getPackageData();

        foreach ($typePackageTemplate as $blockKey => $block) {
            foreach ($block['template'] as $elementKey => $element) {
                if ($element['type'] === 'lib_participant') {
                    if (isset($packageData[$blockKey][$elementKey]['participant_bought'])
                        && isset($packageData[$blockKey][$elementKey]['participant'])
                        && true === $packageData[$blockKey][$elementKey]['participant']) {
                        return intval($packageData[$blockKey][$elementKey]['participant_bought']);
                    } else {
                        return 0;
                    }
                }
            }
        }
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getRemainingPossibleParticipantToBuy(Sheet $sheet)
    {
        $max    = $sheet->getType()->getMaxParticipant();
        $free   = $sheet->getType()->getFreeParticipant();
        $bought = $this->getBoughtParticipant($sheet);

        return intval($max - ($bought + $free));
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getBuyQuantityParticipant(Sheet $sheet)
    {
        $max    = $sheet->getType()->getMaxParticipant();
        $free   = $sheet->getType()->getFreeParticipant();

        return intval($max - $free);
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getRemainingPossibleParticipant(Sheet $sheet)
    {
        $max   = $sheet->getType()->getMaxParticipant();
        $added = count($sheet->getParticipants());

        return $max - $added;
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function canBuyParticipant(Sheet $sheet)
    {
        if ($this->getRemainingPossibleParticipant($sheet) === 0 || $this->getRemainingPossibleParticipant($sheet) < 0) {
            return false;
        }

        if ($this->getRemainingPossibleParticipantToBuy($sheet) === 0 || $this->getRemainingPossibleParticipantToBuy($sheet) < 0) {
            return false;
        }

        return true;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function availableAddParticipant(Sheet $sheet)
    {
        if ($this->getRemainingPossibleParticipant($sheet) === 0) {
            return 0;
        }

        return $this->getBoughtParticipant($sheet) + $sheet->getType()->getFreeParticipant() - count($sheet->getParticipants());
    }

    /**
     * @param Sheet $sheet
     *
     * @return int|null
     */
    public function getParticipantPrice(Sheet $sheet)
    {
        $packageData = $sheet->getTypePackageTemplate();

        foreach ($packageData as $template) {
            foreach ($template['template'] as $block) {
                if ($block['type'] === 'lib_participant') {
                    return isset($block['unitPrice']) ? $block['unitPrice'] : null;
                }
            }
        }

        return null;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int|null
     */
    public function getPlanningPrice(Sheet $sheet)
    {
        $packageData = $sheet->getTypePackageTemplate();

        foreach ($packageData as $template) {
            foreach ($template['template'] as $block) {
                if ($block['type'] === 'lib_planning') {
                    return isset($block['unitPrice']) ? $block['unitPrice'] : null;
                }
            }
        }

        return null;
    }

    public function getAddedBoughtParticipant(Sheet $sheet)
    {
        $freeParticipant = $sheet->getType()->getFreeParticipant();
        $participants    = count($sheet->getParticipants());

        return $participants - $freeParticipant;

    }
}
