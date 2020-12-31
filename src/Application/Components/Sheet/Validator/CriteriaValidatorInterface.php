<?php

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
