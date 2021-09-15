<?php

namespace Proximum\Vimeet\Application\View\Happening;

class HappeningCategoryView
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $picto;

    /**
     * @var string
     */
    private $leftColor;

    /**
     * @var string
     */
    private $rightColor;

    /**
     * HappeningCategoryView constructor.
     *
     * @param string $title
     * @param string $picto
     * @param string $leftColor
     * @param string $rightColor
     */
    public function __construct($title, $picto, $leftColor, $rightColor)
    {
        $this->title      = $title;
        $this->picto      = $picto;
        $this->leftColor  = $leftColor;
        $this->rightColor = $rightColor;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getPicto()
    {
        return $this->picto;
    }

    /**
     * @return string
     */
    public function getLeftColor()
    {
        return $this->leftColor;
    }

    /**
     * @return string
     */
    public function getRightColor()
    {
        return $this->rightColor;
    }
}
