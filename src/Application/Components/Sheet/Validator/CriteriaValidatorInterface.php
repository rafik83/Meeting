<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Validator;

use Proximum\Vimeet\Domain\Model\Sheet;

interface CriteriaValidatorInterface
{
    const YES     = true;
    const NO      = false;
    const ABSTAIN = null;

    /**
     * @param Sheet $sheet
     *
     * @return bool|null
     */
    public function isValid(Sheet $sheet);
}
