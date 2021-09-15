<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class PaginatedCatalogSheetPreviewViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters;

    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /** @var string */
    public $locale;

    /** @var Sheet */
    public $viewer;

    /** @var User */
    public $user;

    /** @var array */
    public $availableSlotIds;

    /** @var array */
    public $sheetsToExclude;

    /** @var bool */
    public $showCategory;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     * @param Sheet  $viewer
     * @param User   $user
     * @param array  $availableSlotIds
     * @param array  $sheetsToExclude
     * @param bool   $showCategory
     */
    public function __construct(
        Event $event,
        array $filters,
        $page,
        $limit,
        $locale,
        Sheet $viewer,
        User $user,
        array $availableSlotIds = [],
        array $sheetsToExclude = [],
        bool $showCategory = false
    ) {
        $this->event = $event;
        $this->filters = $filters;
        $this->page = $page;
        $this->limit = $limit;
        $this->locale = $locale;
        $this->viewer = $viewer;
        $this->user = $user;
        $this->availableSlotIds = $availableSlotIds;
        $this->sheetsToExclude  = $sheetsToExclude;
        $this->showCategory = $showCategory;
    }
}
