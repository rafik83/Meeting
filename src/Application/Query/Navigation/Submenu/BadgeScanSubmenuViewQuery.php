<?php


namespace Proximum\Vimeet\Application\Query\Navigation\Submenu;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\User;

class BadgeScanSubmenuViewQuery implements Query
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

    /** @var null|StaticFormulation */
    public $staticFormulation;

    public function __construct(
        User $user,
        Event $event,
        string $locale,
        Sheet $sheet,
        string $route,
        ?StaticFormulation $staticFormulation = null
    ) {
        $this->user = $user;
        $this->event = $event;
        $this->locale = $locale;
        $this->sheet = $sheet;
        $this->route = $route;
        $this->staticFormulation = $staticFormulation;
    }
}
