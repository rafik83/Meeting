<?php

namespace Proximum\Vimeet\Domain\Model\Product;

class FeatureTranslation
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
     * @var Feature
     */
    private $feature;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @param Feature $feature
     * @param string  $locale
     * @param string  $title
     * @param string  $description
     */
    public function __construct(Feature $feature, $locale, $title, $description)
    {
        $this->feature     = $feature;
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
     * @return Feature
     */
    public function getFeature()
    {
        return $this->feature;
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
     * @return FeatureTranslation
     */
    public function set($title, $description)
    {
        $this->title       = $title;
        $this->description = $description;

        return $this;
    }
}
