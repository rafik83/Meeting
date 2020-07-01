<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Rule;

use Proximum\Vimeet\Domain\Model\Rule;

class SeeWhat
{
    /**
     * @var Rule
     */
    public $rule;

    /**
     * @var array
     */
    public $seeWhat;

    /**
     * @var int
     */
    public $priority;

    /**
     * @var int
     */
    public $phoneAccessMinEvaluation;

    /**
     * @var int
     */
    public $emailAccessMinEvaluation;

    /**
     * @param Rule $rule
     */
    public function __construct(Rule $rule)
    {
        $this->rule = $rule;
        $this->priority = $rule->getPriority();
        $this->seeWhat = $rule->getWhat();
        $this->phoneAccessMinEvaluation = $rule->getPhoneAccessMinEvaluation();
        $this->emailAccessMinEvaluation = $rule->getEmailAccessMinEvaluation();
    }
}
