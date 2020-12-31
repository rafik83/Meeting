<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

use Proximum\Vimeet\Domain\Model\Happening;

class Talking
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Speaker
     */
    private $speaker;

    /**
     * @var Happening
     */
    private $happening;

    /**
     * @var int
     */
    private $position;

    /**
     * Talking constructor.
     *
     * @param Speaker   $speaker
     * @param Happening $happening
     * @param int       $position
     */
    public function __construct(Speaker $speaker, Happening $happening, $position = 0)
    {
        $this->speaker   = $speaker;
        $this->happening = $happening;
        $this->position  = $position;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get speaker
     *
     * @return Speaker
     */
    public function getSpeaker()
    {
        return $this->speaker;
    }

    /**
     * Get happening
     *
     * @return Happening
     */
    public function getHappening()
    {
        return $this->happening;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param Speaker $speaker
     * @param int     $position
     */
    public function update(Speaker $speaker, $position)
    {
        $this->speaker  = $speaker;
        $this->position = $position;
    }
}
