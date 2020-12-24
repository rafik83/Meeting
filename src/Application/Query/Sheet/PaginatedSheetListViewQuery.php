<?php

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\ConditionRules\View\RuleInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class PaginatedSheetListViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $filters;

    /**
     * @var int
     */
    public $page;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Admin
     */
    public $admin;

    /** @var RuleInterface */
    public $condition;

    public function __construct(
        Event $event,
        array $filters,
        $page,
        $limit,
        $locale,
        Admin $admin,
        RuleInterface $condition = null
    ) {
        $this->event = $event;
        $this->filters = $filters;
        $this->page = $page;
        $this->limit = $limit;
        $this->locale = $locale;
        $this->admin = $admin;
        $this->condition = $condition;
    }
}
