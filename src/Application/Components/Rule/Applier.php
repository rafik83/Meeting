<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Rule;

use Proximum\Vimeet\Application\Components\Rule\Strategy\StrategyInterface;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;

class Applier
{
    /**
     * @param Rule              $rule
     * @param Sheet             $sheet
     * @param StrategyInterface $strategy
     */
    public function apply(Rule $rule, Sheet $sheet, StrategyInterface $strategy)
    {
        $what = array_merge(['sheet' => [], 'participant' => []], $rule->getWhat());

        // Apply rule on sheet data
        $sheet->setData($strategy->apply($sheet->getData(), $what['sheet']));

        // Appy rule on participants data
        foreach ($sheet->getParticipants() as $participant) {
            $participant->setData($strategy->apply($sheet->getData(), $what['participant']));
        }
    }
}
