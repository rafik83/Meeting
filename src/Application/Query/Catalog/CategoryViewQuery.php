<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;

class CategoryViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var Category[] */
    public $visibleCategories;

    /** @var string */
    public $locale;

    /**
     * @param Event      $event
     * @param Category[] $visibleCategories
     * @param string     $locale
     */
    public function __construct(Event $event, array $visibleCategories, string $locale)
    {
        $this->event             = $event;
        $this->visibleCategories = $visibleCategories;
        $this->locale            = $locale;
    }
}
