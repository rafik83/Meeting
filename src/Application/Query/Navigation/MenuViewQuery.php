<?php

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\User;

class MenuViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var null|Sheet */
    public $sheet;

    /** @var null|User */
    public $user;

    /** @var StaticFormulation[] */
    public $staticFormulationsIndexedByCategories;

    /**
     * @param Event               $event
     * @param string              $locale
     * @param null|Sheet          $sheet
     * @param null|User           $user
     * @param StaticFormulation[] $staticFormulationsIndexedByCategories
     */
    public function __construct(
        Event $event,
        $locale,
        Sheet $sheet = null,
        User $user = null,
        array $staticFormulationsIndexedByCategories = []
    ) {
        $this->event  = $event;
        $this->locale = $locale;
        $this->sheet  = $sheet;
        $this->user   = $user;
        $this->staticFormulationsIndexedByCategories = $staticFormulationsIndexedByCategories;
    }
}
