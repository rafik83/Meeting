<?php

namespace Proximum\Vimeet\Application\Command\Product\Option;

use Proximum\Vimeet\Application\Command\Product\AbstractCreate;
use Proximum\Vimeet\Domain\Model\Happening;

class CreateOption extends AbstractCreate
{
    /** @var bool */
    public $attributable = false;

    /** @var Happening[] */
    public $happenings = [];
}
