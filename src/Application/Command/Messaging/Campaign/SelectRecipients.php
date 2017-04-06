<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
