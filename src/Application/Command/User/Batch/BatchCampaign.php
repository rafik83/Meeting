<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class BatchCampaign implements Command
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var array */
    public $ids;

    /** @var string */
    public $campaignTitle;

    public function __construct(Event $event, string $locale, array $ids, string $campaignTitle)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->ids = $ids;
        $this->campaignTitle = $campaignTitle;
    }
}
