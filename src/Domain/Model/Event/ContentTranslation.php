<?php

namespace Proximum\Vimeet\Domain\Model\Event;

class ContentTranslation
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
     * @var Content
     */
    private $content;

    /**
     * @var string
     */
    private $value;

    /**
     * @param Content $content
     * @param string  $locale
     * @param string  $value
     */
    public function __construct(Content $content, $locale, $value)
    {
        $this->content = $content;
        $this->locale  = $locale;
        $this->value   = $value;
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
     * @return Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function set($value)
    {
        $this->value = $value;
    }
}
