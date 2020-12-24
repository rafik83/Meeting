<?php

namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\User;

class CatalogSubmenuViewQuery implements Query
{
    /** @var User */
    public $user;

    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $route;

    /** @var StaticFormulation[] */
    public $staticFormulationsIndexedByCategory;

    /**
     * @param User                $user
     * @param Event               $event
     * @param string              $locale
     * @param Sheet               $sheet
     * @param string              $route
     * @param StaticFormulation[] $staticFormulationsIndexedByCategory
     */
    public function __construct(
        User $user,
        Event $event,
        $locale,
        Sheet $sheet,
        $route,
        array $staticFormulationsIndexedByCategory
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->locale = $locale;
        $this->sheet = $sheet;
        $this->route = $route;
        $this->staticFormulationsIndexedByCategory = $staticFormulationsIndexedByCategory;
    }
}
