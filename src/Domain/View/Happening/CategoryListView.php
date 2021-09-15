<?php

namespace Proximum\Vimeet\Domain\View\Happening;

class CategoryListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $rank;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $picto;

    /**
     * @var string
     */
    public $leftColor;

    /**
     * @var string
     */
    public $rightColor;

    /**
     * CategoryListView constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $picto
     * @param int    $rank
     * @param string $leftColor
     * @param string $rightColor
     */
    public function __construct($id, $title, $picto, $rank, $leftColor, $rightColor)
    {
        $this->id         = $id;
        $this->title      = $title;
        $this->picto      = $picto;
        $this->rank       = $rank;
        $this->leftColor  = $leftColor;
        $this->rightColor = $rightColor;
    }
}
