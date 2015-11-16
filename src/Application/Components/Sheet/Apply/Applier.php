<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Apply;

use Proximum\Vimeet\Application\Components\Sheet\Apply\Strategy\StrategyInterface;
use Proximum\Vimeet\Domain\Model\See;
use Proximum\Vimeet\Domain\Model\Sheet;

class Applier
{
    /**
     * @param See               $see
     * @param Sheet             $sheet
     * @param StrategyInterface $strategy
     */
    public function apply(See $see, Sheet $sheet, StrategyInterface $strategy)
    {
        $what = array_merge(['sheet' => [], 'participant' => []], $see->getWhat());

        $sheet->setData($strategy->apply($sheet->getData(), $what['sheet']));

        foreach ($sheet->getParticipants() as $participant) {
            $participant->setData($strategy->apply($sheet->getData(), $what['participant']));
        }
    }
}
