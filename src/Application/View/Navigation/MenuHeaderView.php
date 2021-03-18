<?php

namespace Proximum\Vimeet\Application\View\Navigation;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class MenuHeaderView
{
    /**
     * @var bool
     */
    private $notification;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var array
     */
    private $localeRoutes;

    /**
     * @var null|Sheet
     */
    private $sheet;

    /**
     * @var bool
     */
    private $multipleSheets;

    /**
     * MenuHeaderView constructor.
     *
     * @param Event      $event
     * @param array      $localeRoutes
     * @param Sheet|null $sheet
     * @param bool       $notification
     * @param bool       $multipleSheets
     */
    public function __construct(
        Event $event,
        array $localeRoutes,
        Sheet $sheet = null,
        $notification = false,
        $multipleSheets = false
    ) {
        $this->notification   = $notification;
        $this->event          = $event;
        $this->localeRoutes   = $localeRoutes;
        $this->sheet          = $sheet;
        $this->multipleSheets = $multipleSheets;
    }

    /**
     * @return null|Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return bool
     */
    public function hasNotifications()
    {
        return $this->notification;
    }

    /**
     * @return array
     */
    public function getLocaleRoutes()
    {
        return $this->localeRoutes;
    }

    /**
     * @return bool
     */
    public function hasSheet()
    {
        return null !== $this->sheet;
    }

    /**
     * @return bool
     */
    public function hasMultipleSheets()
    {
        return $this->multipleSheets;
    }
}
