<?php

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\User;

class SubmenuViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var string */
    public $route;

    /** @var null|Sheet */
    public $sheet;

    /** @var null|User */
    public $user;

    /** @var StaticFormulation[] */
    public $staticFormulationsIndexByCategories;

    /**
     * @param Event               $event
     * @param string              $locale
     * @param string              $route
     * @param null|Sheet          $sheet
     * @param null|User           $user
     * @param StaticFormulation[] $staticFormulationsIndexByCategories
     */
    public function __construct(
        Event $event,
        $locale,
        $route,
        Sheet $sheet = null,
        User $user = null,
        array $staticFormulationsIndexByCategories = []
    ) {
        $this->event = $event;
        $this->locale = $locale;
        $this->route = $route;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->staticFormulationsIndexByCategories = $staticFormulationsIndexByCategories;
    }
}
