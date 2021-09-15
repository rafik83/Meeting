<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class Media
{
    /**
     * @var MediaCollection
     */
    private $collection;

    /**
     * @var array|string
     */
    private $title;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    public $type;

    /**
     * Media constructor.
     *
     * @param MediaCollection $collection
     * @param array|string    $title
     * @param string          $url
     * @param string          $type
     */
    public function __construct(MediaCollection $collection, $title, $url, $type)
    {
        $this->collection = $collection;
        $this->title      = $title;
        $this->url        = $url;
        $this->type       = $type;

        $translatable     = $this->collection->isTranslatable();
        $fallback         = $this->collection->getFallback();

        // If the object is translatable but data is not translated, make an array for the fallback locale
        // If the object isn't translatable but data is translated, get the fallback data if exist, the first data else
        if ($translatable && !\is_array($this->title)) {
            $this->title = [$fallback => $this->title];
        } elseif (!$translatable && \is_array($this->title)) {
            $this->title = isset($this->title[$fallback]) ? $this->title[$fallback] : reset($this->title);
        }
    }

    /**
     * Get collection
     *
     * @return MediaCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get fallback title if object is translatable.
     *
     * @return string|null
     */
    public function getFallbackTitle()
    {
        if ($this->collection->isTranslatable() && \is_array($this->title) || \is_array($this->title)) {
            return isset($this->title[$this->collection->getFallback()])
                ? $this->title[$this->collection->getFallback()]
                : null;
        }

        return null;
    }

    /**
     * Get title in the current locale
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->collection->isTranslatable() && \is_array($this->title) || \is_array($this->title)) {
            return isset($this->title[$this->collection->getLocale()])
                ? $this->title[$this->collection->getLocale()]
                : null;
        }

        return $this->title;
    }

    /**
     * Set title in the current locale
     *
     * @param string $title
     *
     * @return Media
     */
    public function setTitle($title)
    {
        if ($this->collection->isTranslatable()) {
            if (!\is_array($this->title)) {
                $this->title = [];
            }

            $this->title[$this->collection->getLocale()] = $title;
        } else {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'title' => $this->title,
            'url'   => $this->url,
            'type'  => $this->type,
        ];
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return null === $this->title || (\is_array($this->title) && 0 === \count(array_filter($this->title)));
    }
}
