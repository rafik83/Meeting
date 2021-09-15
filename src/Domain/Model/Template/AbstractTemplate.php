<?php

namespace Proximum\Vimeet\Domain\Model\Template;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

abstract class AbstractTemplate
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $value = [];

    /**
     * @var array
     */
    protected $locales = [];

    /**
     * @var string
     */
    protected $fallback;

    /**
     * @var null|Event
     */
    protected $event;

    /**
     * @var ArrayCollection of Type
     */
    protected $types;

    /**
     * @var DateTimeInterface
     */
    protected $createdAt;

    /**
     * @return string
     */
    abstract public function getFallback();

    /**
     * @param string             $title
     * @param array              $value
     * @param array              $locales
     * @param string             $fallback
     * @param \DateTimeInterface $createdAt
     * @param Event              $event
     */
    public function __construct(
        $title,
        array $value,
        array $locales,
        $fallback,
        \DateTimeInterface $createdAt,
        Event $event = null
    ) {
        $this->title     = $title;
        $this->value     = $value;
        $this->fallback  = $fallback;
        $this->createdAt = $createdAt;
        $this->event     = $event;
        $this->types     = new ArrayCollection();

        foreach ($locales as $locale) {
            $this->addLocale($locale);
        }

        if (!$this->hasLocale($fallback)) {
            throw new \InvalidArgumentException('Default locale should be in the template locales.');
        }
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
     * @return null|Event
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @return bool
     */
    public function hasEvent(): bool
    {
        return $this->getEvent() instanceof Event;
    }

    /**
     * @param null|Event $event
     */
    public function setEvent(Event $event = null)
    {
        $this->event = $event;

        // Add event locales
        if ($event) {
            foreach ($event->getLocales() as $locale) {
                $this->addLocale($locale);
            }
        }
    }

    /**
     * @return Type[]
     */
    public function getTypes(): array
    {
        return $this->types->toArray();
    }

    public function hasType(Type $type): bool
    {
        return \in_array($type, $this->getTypes(), true);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get locales
     *
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }

    /**
     * @param string $locale
     *
     * @return AbstractTemplate
     */
    public function addLocale($locale)
    {
        if (!$this->hasLocale($locale) && null !== $locale) {
            $this->locales[] = $locale;
            $this->fixValue([$locale]);
        }

        return $this;
    }

    /**
     * @param array  $locales
     * @param string $fallback
     *
     * @return AbstractTemplate
     */
    public function updateLocales(array $locales, $fallback)
    {
        $this->fallback = $fallback;

        foreach ($locales as $locale) {
            if (!$this->hasLocale($locale)) {
                $this->addLocale($locale);
            }
        }

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return bool
     */
    public function hasLocale($locale)
    {
        return in_array($locale, $this->locales);
    }

    /**
     * Get locales available for the current event if set, else get all locales.
     *
     * @return array
     */
    public function getEnabledLocales()
    {
        return $this->event ? array_filter($this->locales, function ($locale) {
            return $this->event->hasLocale($locale);
        }) : $this->locales;
    }

    /**
     * Get value
     *
     * @return array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set value
     *
     * @param array $value
     *
     * @return AbstractTemplate
     */
    public function setValue(array $value)
    {
        $this->value = $value;

        $this->fixValue($this->locales);

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Consolidate value for each locales
     *
     * @param array $locales
     */
    protected function fixValue(array $locales)
    {
        foreach ($locales as $locale) {
            $this->value = self::createLocale($this->value, $locale);
        }
    }

    /**
     * @param array  $config
     * @param string $locale
     *
     * @return array
     */
    protected static function createLocale($config, $locale)
    {
        $keys = ['label', 'help', 'placeholder', 'titlePlaceholder', 'linkPlaceholder'];

        if (!isset($config['component'])) {
            return self::createComponents($config, $locale);
        }

        if ('block' === $config['component']) {
            return self::createBlock($config, $locale);
        }

        if ('object' === $config['component']) {
            return self::createObject($config, $locale, $keys);
        }

        return $config;
    }

    /**
     * @param $config
     * @param $locale
     *
     * @return array
     */
    protected static function createComponents($config, $locale)
    {
        return array_map(
            function ($item) use ($locale) {
                return self::createLocale($item, $locale);
            },
            $config
        );
    }

    /**
     * @param $config
     * @param $locale
     *
     * @return mixed
     */
    protected static function createBlock($config, $locale)
    {
        foreach ($config['children'] as $key => $column) {
            $config['children'][$key] = self::createLocale($column, $locale);
        }

        return $config;
    }

    /**
     * @param $config
     * @param $locale
     * @param $keys
     *
     * @return mixed
     */
    protected static function createObject($config, $locale, $keys)
    {
        foreach ($config['config'] as $key => $value) {
            if (in_array($key, $keys) || 'text' === $config['type'] && 'content' === $key) {
                $config['config'][$key] = array_merge([$locale => null], $config['config'][$key]);
            }
        }

        return $config;
    }
}
