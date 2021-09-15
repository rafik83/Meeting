<?php

namespace Proximum\Vimeet\Domain\Model;

class AbstractCategory
{
    const PICTO_COCKTAIL   = 'Cocktail';
    const PICTO_LUNCH      = 'Dejeuner';
    const PICTO_CONFERENCE = 'Conference';
    const PICTO_MEETING    = 'RDV';
    const PICTO_FLASH      = 'PresFlash_2';
    const PICTO_COFFEE     = 'Cafe_1';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @var string
     */
    protected $picto;

    /**
     * @var string
     */
    protected $leftColor;

    /**
     * @var string
     */
    protected $rightColor;

    /**
     * AbstractCategory constructor.
     *
     * @param Event  $event
     * @param string $picto
     * @param string $leftColor
     * @param string $rightColor
     */
    public function __construct(Event $event, $picto, $leftColor, $rightColor)
    {
        $this->event      = $event;
        $this->picto      = $picto;
        $this->leftColor  = $leftColor;
        $this->rightColor = $rightColor;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getPicto()
    {
        return $this->picto;
    }

    /**
     * @param string $picto
     */
    public function setPicto($picto)
    {
        $this->picto = $picto;
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

    /**
     * @param string $leftColor
     */
    public function setLeftColor($leftColor)
    {
        $this->leftColor = $leftColor;
    }

    /**
     * @param string $rightColor
     */
    public function setRightColor($rightColor)
    {
        $this->rightColor = $rightColor;
    }

    /**
     * @return array
     */
    public static function getPictos()
    {
        return [
            self::PICTO_COCKTAIL   => self::PICTO_COCKTAIL,
            self::PICTO_LUNCH      => self::PICTO_LUNCH,
            self::PICTO_CONFERENCE => self::PICTO_CONFERENCE,
            self::PICTO_MEETING    => self::PICTO_MEETING,
            self::PICTO_FLASH      => self::PICTO_FLASH,
            self::PICTO_COFFEE     => self::PICTO_COFFEE,
        ];
    }
}
