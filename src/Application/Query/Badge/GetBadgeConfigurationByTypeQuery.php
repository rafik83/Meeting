<?php

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Type;

class GetBadgeConfigurationByTypeQuery implements Query
{
    /** @var Type */
    public $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }
}
