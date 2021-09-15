<?php

namespace Proximum\Vimeet\Application\View\Tip;

abstract class AbstractTipView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /**
     * AbstractTipView constructor.
     *
     * @param int    $id
     * @param string $title
     */
    public function __construct($id, $title)
    {
        $this->id    = $id;
        $this->title = $title;
    }
}
