<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Happening;

class GetBroadcastViewQuery implements Query
{
    /** @var Happening */
    public $happening;

    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
    }
}
