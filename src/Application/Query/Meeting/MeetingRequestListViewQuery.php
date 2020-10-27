<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class MeetingRequestListViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    /** @var array */
    public $filters;

    /** @var array */
    public $slotsToFilter;

    /** @var Event */
    public $event;

    /** @var bool */
    public $showCategory;

    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        string $locale,
        array $filters = [],
        array $slotsToFilter = [],
        bool $showCategory = false
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->locale = $locale;
        $this->filters = $filters;
        $this->slotsToFilter = $slotsToFilter;
        $this->showCategory = $showCategory;
    }
}
