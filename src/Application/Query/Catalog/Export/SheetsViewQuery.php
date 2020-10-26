<?php

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetsViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var array */
    public $filters;

    /** @var string */
    public $locale;

    /** @var array */
    public $availableSlotIds;

    /** @var Sheet[] */
    public $sheetsToExclude;

    /** @var bool boolean to determine if the catalog display type or category */
    public $isTypeColumn;

    /**
     * @param Sheet[] $sheetsToExclude
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        array $filters,
        string $locale,
        array $availableSlotIds = [],
        array $sheetsToExclude = [],
        bool $isTypeColumn
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->filters = $filters;
        $this->locale = $locale;
        $this->availableSlotIds = $availableSlotIds;
        $this->sheetsToExclude = $sheetsToExclude;
        $this->isTypeColumn = $isTypeColumn;
    }
}
