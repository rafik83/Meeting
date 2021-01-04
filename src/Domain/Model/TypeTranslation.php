<?php

namespace Proximum\Vimeet\Domain\Model;

class TypeTranslation
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
     * @var Type
     */
    private $type;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * TypeTranslation constructor.
     *
     * @param Type   $type
     * @param string $locale
     * @param string $title
     * @param string $description
     */
    public function __construct(Type $type, $locale, $title, $description = '')
    {
        $this->type        = $type;
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
     * @return Type
     */
    public function getType()
    {
        return $this->type;
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
     *
     * @return TypeTranslation
     */
    public function update($title, $description = '')
    {
        $this->title       = $title;
        $this->description = $description;

        return $this;
    }
}
