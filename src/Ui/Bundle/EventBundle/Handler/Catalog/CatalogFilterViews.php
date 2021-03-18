<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class CatalogFilterViews
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     */
    public function __construct(Event $event, Sheet $sheet, string $locale)
    {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->locale = $locale;
    }
}
