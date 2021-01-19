<?php

namespace Proximum\Vimeet\Domain\Model\Tip;

class TipTranslation
{
    /** @var int */
    private $id;

    /** @var Tip */
    private $tip;

    /** @var string */
    private $title;

    /** @var string */
    private $locale;

    /** @var string */
    private $content;

    /** @var \DateTimeInterface */
    private $createdAt;

    /**
     * TipTranslation constructor.
     *
     * @param Tip                $tip
     * @param \DateTimeInterface $createdAt
     * @param string|null        $title
     * @param string             $locale
     * @param string             $content
     */
    public function __construct(Tip $tip, \DateTimeInterface $createdAt, $title, $locale, $content)
    {
        $this->tip       = $tip;
        $this->createdAt = $createdAt;
        $this->title     = $title;
        $this->locale    = $locale;
        $this->content   = $content;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $content
     *
     * @return TipTranslation $this
     */
    public function set($locale, $title, $content)
    {
        $this->locale  = $locale;
        $this->title   = $title;
        $this->content = $content;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Tip
     */
    public function getTip()
    {
        return $this->tip;
    }

    /**
     * @param Tip $tip
     *
     * @return $this
     */
    public function setTip(Tip $tip)
    {
        $this->tip = $tip;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
