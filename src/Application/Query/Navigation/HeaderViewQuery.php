<?php

namespace Proximum\Vimeet\Application\Query\Navigation;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class HeaderViewQuery implements Query
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var bool
     */
    public $registration;

    /**
     * @var string
     */
    public $route;

    /**
     * @var array
     */
    public $routeParameters;

    /**
     * @var null|Sheet
     */
    public $sheet;

    /**
     * @var User|null
     */
    public $user;

    /**
     * @param Event      $event
     * @param string     $locale
     * @param string     $route
     * @param array      $routeParameters
     * @param bool       $registration
     * @param null|Sheet $sheet
     * @param null|User  $user
     */
    public function __construct(
        Event $event,
        $locale,
        $route,
        array $routeParameters,
        $registration,
        Sheet $sheet = null,
        User $user = null
    ) {
        $this->event           = $event;
        $this->locale          = $locale;
        $this->registration    = $registration;
        $this->route           = $route;
        $this->routeParameters = $routeParameters;
        $this->sheet           = $sheet;
        $this->user            = $user;
    }
}
