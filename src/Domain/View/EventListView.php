<?php

namespace Proximum\Vimeet\Domain\View;

use Proximum\Vimeet\Application\View\Event\DayView;
use Proximum\Vimeet\Domain\Model\EventInterface;

class EventListView implements EventInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $domain;

    /**
     * @var array
     */
    public $locales;

    /**
     * @var string
     */
    public $fallback;

    /**
     * @var DayView[]
     */
    public $days;

    /** @var bool */
    public $visible;

    /** @var bool */
    public $visio;

    /** @var bool */
    public $accessControlEnabled;

    /**
     * @param null|int  $id
     * @param string    $title
     * @param string    $domain
     * @param array     $locales
     * @param string    $fallback
     * @param bool      $visible
     * @param DayView[] $days
     * @param bool      $visio
     * @param bool      $accessControlEnabled
     */
    public function __construct($id, $title, $domain, array $locales, $fallback, bool $visible, array $days = [], bool $visio = false, $accessControlEnabled = false)
    {
        $this->id = $id;
        $this->title = $title;
        $this->domain = $domain;
        $this->locales = $locales;
        $this->fallback = $fallback;
        $this->visible = $visible;
        $this->days = $days;
        $this->visio = $visio;
        $this->accessControlEnabled = $accessControlEnabled;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
