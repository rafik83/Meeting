<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\Message;

class SelectMessage
{
    /** @var Campaign */
    public $campaign;

    /** @var Message */
    public $message;

    /**
     * @param Campaign $campaign
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }
}
