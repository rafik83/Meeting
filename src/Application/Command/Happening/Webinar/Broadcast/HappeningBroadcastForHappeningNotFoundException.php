<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use Proximum\Vimeet\Domain\Model\Happening;

class HappeningBroadcastForHappeningNotFoundException extends \RuntimeException
{
    public function __construct(Happening $happening)
    {
        parent::__construct('No broadcast found for happening ' . $happening->getId());
    }
}
