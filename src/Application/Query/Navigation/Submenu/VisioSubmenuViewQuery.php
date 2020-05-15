<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;

class VisioSubmenuViewQuery implements Query
{
    /** @var string */
    public $locale;

    /** @var string */
    public $route;

    /** @var null|StaticFormulation */
    public $staticFormulation;

    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    public function __construct(
        Event $event,
        Sheet $sheet,
        string $locale,
        string $route,
        ?StaticFormulation $staticFormulation = null
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->locale = $locale;
        $this->route  = $route;
        $this->staticFormulation = $staticFormulation;
    }
}
