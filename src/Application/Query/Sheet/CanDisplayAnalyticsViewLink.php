<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class CanDisplayAnalyticsViewLink
{
    public function isSatisfiedBy(Sheet $sheet): bool
    {
        return $sheet->isInInternalCatalog() && $sheet->getType()->canDisplayAnalyticsOnSheet()
            && $sheet->getType()->canDisplayAnalyticsOnCatalog()
            && $sheet->getAnalytics()->getViews() > 0;
    }
}
