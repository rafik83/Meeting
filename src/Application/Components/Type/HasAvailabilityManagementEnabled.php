<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Type;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
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
