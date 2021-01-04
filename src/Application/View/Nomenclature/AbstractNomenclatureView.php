<?php

namespace Proximum\Vimeet\Application\View\Nomenclature;

use Proximum\Vimeet\Domain\Model\Event;

abstract class AbstractNomenclatureView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var int */
    public $depth;

    /** @var bool */
    public $deletable;

    /** @var null|Event */
    public $event;

    /**
     * @param int        $id
     * @param string     $title
     * @param int        $depth
     * @param bool       $deletable
     * @param Event|null $event
     */
    public function __construct(int $id, string $title, int $depth, bool $deletable = false, Event $event = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->depth = $depth;
        $this->deletable = $deletable;
        $this->event = $event;
    }
}
