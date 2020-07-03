<?php

namespace Proximum\Vimeet\Domain\Model\Unavailability;

use Proximum\Vimeet\Domain\Model\AbstractCategory;
use Proximum\Vimeet\Domain\Model\Event;

class Category extends AbstractCategory
{
    /**
     * @var string
     */
    private $title;

    /**
     * @param Event  $event
     * @param string $picto
     * @param string $title
     * @param string $leftColor
     * @param string $rightColor
     */
    public function __construct(Event $event, $picto, $title, $leftColor, $rightColor)
    {
        parent::__construct($event, $picto, $leftColor, $rightColor);

        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $picto
     * @param string $title
     * @param string $leftColor
     * @param string $rightColor
     */
    public function update($picto, $title, $leftColor, $rightColor)
    {
        $this->picto      = $picto;
        $this->title      = $title;
        $this->leftColor  = $leftColor;
        $this->rightColor = $rightColor;
    }
}
