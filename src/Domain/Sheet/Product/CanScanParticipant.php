<?php
/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet\Product;

use Proximum\Vimeet\Domain\Model\Product;

class CanScanParticipant
{
    public function isSatisfiedBy(Product $product): bool
    {
        return $product->canScanParticipant();
    }
}
