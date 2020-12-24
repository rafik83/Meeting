<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Validator;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetAcceptedCriteriaValidator implements CriteriaValidatorInterface
{
    /**
     * @param Sheet $sheet
     *
     * @return bool|null
     */
    public function isValid(Sheet $sheet)
    {
        if (!$sheet->getType()->getValidationCriteria()->isSheetAccepted()) {
            return CriteriaValidatorInterface::ABSTAIN;
        }

        return $sheet->isAccepted() ? CriteriaValidatorInterface::YES : CriteriaValidatorInterface::NO;
    }
}
