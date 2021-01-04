<?php

namespace Proximum\Vimeet\Domain\Model;

class Notification
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var string
     */
    private $type;

    /**
     * Notification constructor.
     *
     * @param Sheet  $sheet
     * @param string $type
     */
    public function __construct(Sheet $sheet, $type)
    {
        $this->sheet = $sheet;
        $this->type  = $type;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
