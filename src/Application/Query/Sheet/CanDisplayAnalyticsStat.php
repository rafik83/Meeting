<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class CanDisplayAnalyticsStat
{
    public function isSatisfiedBy(Sheet $sheet): bool
    {
        return $sheet->isInInternalCatalog() && $sheet->getType()->canDisplayAnalyticsOnSheet();
    }
}
