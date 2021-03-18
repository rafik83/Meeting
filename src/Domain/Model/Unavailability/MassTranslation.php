<?php

namespace Proximum\Vimeet\Domain\Model\Unavailability;

class MassTranslation
{
    /** @var int */
    private $id;

    /** @var string */
    private $locale;

    /** @var Mass */
    private $mass;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /**
     * @param Mass   $mass
     * @param string $locale
     * @param string $title
     * @param string $description
     */
    public function __construct(
        Mass $mass,
        $locale,
        $title,
        $description
    ) {
        $this->mass        = $mass;
        $this->locale      = $locale;
        $this->title       = $title;
        $this->description = $description;
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
     * @return Mass
     */
    public function getMass()
    {
        return $this->mass;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $title
     * @param string $description
     */
    public function update($title, $description)
    {
        $this->title       = $title;
        $this->description = $description;
    }
}
