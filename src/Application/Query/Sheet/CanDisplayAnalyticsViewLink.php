<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;

class CanDisplayAnalyticsViewLink
{
    public function isSatisfiedBy(Sheet $sheet): bool
    {
        return $sheet->isInInternalCatalog() && $sheet->getType()->displayAnalyticsOnSheet
            && $sheet->getType()->displayAnalyticsOnCatalog
            && $sheet->getAnalytics()->getViews() > 0;
    }
}
