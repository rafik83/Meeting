<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class Batch implements Command
{
    public const SELECTION_TYPE_PAGE = 'selection_type_page';
    public const SELECTION_TYPE_ALL  = 'selection_type_all';

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var array */
    public $ids;

    /** @var string */
    public $campaignTitle;

    /** @var string */
    public $selectionType;

    public function __construct(Event $event, string $locale)
    {
        $this->event = $event;
        $this->locale = $locale;
    }
}
