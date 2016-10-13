<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;

class SearchFacet
{
    const TYPE_TYPE                  = 'type';
    const TYPE_POSITION              = 'position';
    const TYPE_CATEGORY              = 'category';
    const TYPE_ORGANIZATION_CATEGORY = 'organizationCategory';
    const TYPE_KEYWORDS              = 'keywords';
    const TYPE_LOCALIZATION          = 'localization';

    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $type;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * SearchFacet constructor.
     *
     * @param Event  $event
     * @param string $type
     * @param bool   $enabled
     */
    public function __construct(Event $event, $type, $enabled = false)
    {
        $this->event        = $event;
        $this->type         = $type;
        $this->enabled      = $enabled;
        $this->translations = new ArrayCollection();

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = new SearchFacetTranslation($this, '', '', $locale);
        }
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param boolean $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SearchFacetTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param SearchFacetTranslation[] $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return array
     */
    public static function getAllTypes()
    {
        return [
            self::TYPE_CATEGORY,
            self::TYPE_TYPE,
            self::TYPE_POSITION,
            self::TYPE_ORGANIZATION_CATEGORY,
            self::TYPE_KEYWORDS,
            self::TYPE_LOCALIZATION,
        ];
    }

    /**
     * @param string $locale
     * @param string $label
     * @param string $placeholder
     *
     * @return SearchFacet
     */
    public function translate($locale, $label, $placeholder)
    {
        foreach ($this->translations as $translation) {
            if ($translation->getLocale() === $locale) {
                $translation->update($label, $placeholder);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasPlaceholder()
    {
        return !in_array($this->type, [self::TYPE_TYPE, self::TYPE_CATEGORY]);
    }
}
