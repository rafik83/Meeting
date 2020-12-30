<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;

class SelectRecipients
{
    /**
     * @var string[]
     *
     * @see Campaign::getRecipientChoices()
     */
    public $recipients;

    /** @var Campaign */
    public $campaign;

    /**
     * @param Campaign $campaign
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }
}
