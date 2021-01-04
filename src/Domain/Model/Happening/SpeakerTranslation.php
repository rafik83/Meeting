<?php

namespace Proximum\Vimeet\Domain\Model\Happening;

class SpeakerTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Speaker
     */
    private $speaker;

    /**
     * @var string
     */
    private $position;

    /**
     * SpeakerTranslation constructor.
     *
     * @param Speaker $speaker
     * @param string  $locale
     * @param string  $position
     */
    public function __construct(Speaker $speaker, $locale, $position)
    {
        $this->speaker  = $speaker;
        $this->locale   = $locale;
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Speaker
     */
    public function getSpeaker()
    {
        return $this->speaker;
    }

    /**
     * @param Speaker $speaker
     */
    public function setSpeaker($speaker)
    {
        $this->speaker = $speaker;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param string $position
     *
     * @return SpeakerTranslation
     */
    public function update($position)
    {
        $this->position = $position;

        return $this;
    }
}
