<?php

namespace Proximum\Vimeet\Domain\Model\Catalog;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class AbstractSearchFacet
{
    const TYPE_TYPE                  = 'type';
    const TYPE_POSITION              = 'position';
    const TYPE_CATEGORY              = 'category';
    const TYPE_ORGANIZATION_CATEGORY = 'organizationCategory';
    const TYPE_KEYWORDS              = 'keywords';
    const TYPE_LOCALIZATION          = 'localization';

    /** @var int */
    protected $id;

    /** @var Event */
    protected $event;

    /** @var string */
    protected $type;

    /** @var bool */
    protected $enabled;

    /** @var ArrayCollection */
    protected $translations;

    /**
     * AbstractSearchFacet constructor.
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
            $this->translations[$locale] = new AbstractSearchFacetTranslation($this, '', '', $locale);
        }
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
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
    public function getType(): string
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
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
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
     * @return AbstractSearchFacetTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    /**
     * @param AbstractSearchFacetTranslation[] $translations
     */
    public function setTranslations(array $translations)
    {
        $this->translations = new ArrayCollection($translations);
    }

    /**
     * @return array
     */
    public static function getAllTypes(): array
    {
        return [
            self::TYPE_CATEGORY              => self::TYPE_CATEGORY,
            self::TYPE_TYPE                  => self::TYPE_TYPE,
            self::TYPE_POSITION              => self::TYPE_POSITION,
            self::TYPE_ORGANIZATION_CATEGORY => self::TYPE_ORGANIZATION_CATEGORY,
            self::TYPE_KEYWORDS              => self::TYPE_KEYWORDS,
            self::TYPE_LOCALIZATION          => self::TYPE_LOCALIZATION,
        ];
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function hasPlaceholder(string $type): bool
    {
        return !in_array($type, [self::TYPE_TYPE, self::TYPE_CATEGORY], true);
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getLabel() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getPlaceholder($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getPlaceholder() : '';
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function hasTranslation(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }
}
