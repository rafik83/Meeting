<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\Sheet;

final class ExternalCatalog
{
    const DEFAULT_FILTERS = [
        'enabled' => true,
        'state' => [
            Sheet::STATE_ACCEPTED,
            Sheet::STATE_VALIDATED,
        ],
    ];
}
