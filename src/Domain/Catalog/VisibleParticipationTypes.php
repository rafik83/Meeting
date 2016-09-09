<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;

class VisibleParticipationTypes
{
    private $ruleRepository;

    /**
     * AllowedTypes constructor.
     */
    public function __construct(RuleRepositoryInterface $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    public function getAllowedTypesList(Sheet $sheet)
    {
        $type = $sheet->getType();

        $rules = $this->ruleRepository->getByEvent($sheet->getEvent());

        $visibleTypes = array_filter($rules, function ($rule) use ($type) {
            if ($rule->getSeerType() == $type) {
                return $rule->getSeeableType();
            }
        });

        dump($rules);die;
    }
}
