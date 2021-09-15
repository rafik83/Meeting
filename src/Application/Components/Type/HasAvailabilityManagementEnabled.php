<?php

namespace Proximum\Vimeet\Application\Components\Type;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;

class HasAvailabilityManagementEnabled
{
    public function isSatisfiedBy(Sheet $sheet): bool
    {
        $type = $sheet->getType();

        return Type::TYPE_MANAGEMENT_AVAILABLE === $type->getAvailabilityType();
    }
}
