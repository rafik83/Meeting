<?php

namespace Proximum\Vimeet\Domain\Model\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;

class Content
{
    const TYPE_TERMS_OF_SALE = 'terms-of-sale';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @param Event  $event
     * @param string $type
     */
    public function __construct(Event $event, $type)
    {
        $this->event        = $event;
        $this->type         = $type;
        $this->translations = new ArrayCollection();
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return mixed
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function hasTranslation($locale)
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * @param string $locale
     *
     * @return ContentTranslation
     */
    public function getTranslation($locale)
    {
        return $this->translations->get($locale);
    }

    /**
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    public function getValue($locale, $fallback = null)
    {
        return $this->hasTranslation($locale)
            ? $this->getTranslation($locale)->getValue()
            : ($fallback ? $this->getValue($fallback) : '');
    }

    /**
     * @param string $locale
     * @param string $value
     *
     * @return Content
     */
    public function translate($locale, $value)
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->set($value);
        } else {
            $this->translations->set($locale, new ContentTranslation($this, $locale, $value));
        }

        return $this;
    }
}
