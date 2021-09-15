<?php

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
