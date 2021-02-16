<?php

namespace Proximum\Vimeet\Domain\Template;

abstract class AbstractChild
{
    public const TEMPLATE_OBJECT_TYPE_BUTTON_LINK   = 'button-link';
    public const TEMPLATE_OBJECT_TYPE_COLLECTION    = 'collection';
    public const TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT = 'editable-text';
    public const TEMPLATE_OBJECT_TYPE_IMAGE         = 'image';
    public const TEMPLATE_OBJECT_TYPE_MEDIA         = 'medias';
    public const TEMPLATE_OBJECT_TYPE_NOMENCLATURE  = 'nomenclature';
    public const TEMPLATE_OBJECT_TYPE_PARTICIPANT   = 'participant';
    public const TEMPLATE_OBJECT_TYPE_TAG           = 'tag';
    public const TEMPLATE_OBJECT_TYPE_TEXT          = 'text';
    public const TEMPLATE_OBJECT_TYPE_TELEPHONE     = 'telephone';
    public const TEMPLATE_OBJECT_TYPE_COUNTRY       = 'country';
    public const TEMPLATE_OBJECT_TYPE_URL           = 'url';
    public const TEMPLATE_OBJECT_TYPE_TAGS          = 'tags';
    public const TEMPLATE_OBJECT_TYPE_GENDER        = 'gender';
    public const TEMPLATE_OBJECT_TYPE_BOOLEAN       = 'boolean';
    public const TEMPLATE_OBJECT_TYPE_UPLOAD        = 'upload';
    public const TEMPLATE_OBJECT_TYPE_VIDEO         = 'video';
    public const TEMPLATE_OBJECT_TYPE_CHECKBOX      = 'checkbox';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $fallback;

    /**
     * @var string|null
     */
    private $uid;

    /**
     * AbstractChild constructor.
     *
     * @param string $type
     * @param array  $config
     * @param string $locale
     * @param string $fallback
     */
    public function __construct($type, array $config, $locale, $fallback)
    {
        $this->type     = $type;
        $this->config   = $config;
        $this->locale   = $locale;
        $this->fallback = $fallback;
    }

    /**
     * @param string      $name
     * @param null|string $locale
     * @param null|string $fallback
     *
     * @return mixed
     */
    public function getOption($name, $locale = null, $fallback = null)
    {
        if (null === $locale) {
            return isset($this->config[$name]) ? $this->config[$name] : null;
        }

        return isset($this->config[$name][$locale])
            ? $this->config[$name][$locale]
            : ($fallback ? $this->getOption($name, $fallback) : null);
    }

    /**
     * @param string      $name
     * @param mixed       $value
     * @param null|string $locale
     */
    public function setOption($name, $value, $locale = null)
    {
        if (null === $locale) {
            $this->config[$name] = $value;
        } else {
            $this->config[$name][$locale] = $value;
        }
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function isParticipant(): bool
    {
        return self::TEMPLATE_OBJECT_TYPE_PARTICIPANT === $this->type;
    }

    public function isUpload(): bool
    {
        return self::TEMPLATE_OBJECT_TYPE_UPLOAD === $this->type;
    }

    public function isVideo(): bool
    {
        return self::TEMPLATE_OBJECT_TYPE_VIDEO === $this->type;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getFallback()
    {
        return $this->fallback;
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->getOption('style');
    }

    /**
     * @return string
     */
    abstract public function getComponent();

    /**
     * @return array
     */
    abstract public function normalize();

    public function setUid(string $name): void
    {
        $this->uid = $name;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }
}
