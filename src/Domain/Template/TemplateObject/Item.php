<?php

namespace Proximum\Vimeet\Domain\Template\TemplateObject;

class Item
{
    /**
     * @var ItemCollection
     */
    private $collection;

    /**
     * @var array|string
     */
    private $title;

    /**
     * Item constructor.
     *
     * @param ItemCollection $collection
     * @param array|string   $title
     */
    public function __construct(ItemCollection $collection, $title)
    {
        $this->collection = $collection;
        $this->title      = $title;

        $translatable     = $this->collection->isTranslatable();
        $fallback         = $this->collection->getFallback();

        // If the object is translatable but data is not translated, make an array for the fallback locale
        // If the object isn't translatable but data is translated, get the fallback data if exist, the first data else
        if ($translatable && !is_array($this->title)) {
            $this->title = [$fallback => $this->title];
        } elseif (!$translatable && is_array($this->title)) {
            $this->title = isset($this->title[$fallback]) ? $this->title[$fallback] : reset($this->title);
        }
    }

    /**
     * Get collection
     *
     * @return ItemCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Get title in the current locale
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->collection->isTranslatable() || is_array($this->title)) {
            return isset($this->title[$this->collection->getLocale()])
                ? $this->title[$this->collection->getLocale()]
                : null;
        }

        return $this->title;
    }

    /**
     * Get title for the given locale
     *
     * @param string $locale
     *
     * @return string|null
     */
    public function getTitleLocalize($locale)
    {
        if ($this->collection->isTranslatable() || is_array($this->title)) {
            return isset($this->title[$locale])
                ? $this->title[$locale]
                : null;
        }

        return $this->title;
    }

    /**
     * @return array|string
     */
    public function getRawTitle()
    {
        return $this->title;
    }

    /**
     * Get fallback title if object is translatable.
     *
     * @return string|null
     */
    public function getFallbackTitle()
    {
        if ($this->collection->isTranslatable() && is_array($this->title) || is_array($this->title)) {
            return isset($this->title[$this->collection->getFallback()])
                ? $this->title[$this->collection->getFallback()]
                : null;
        }

        return null;
    }

    /**
     * @param string $title
     *
     * @return Item
     */
    public function setTitle($title)
    {
        if ($this->collection->isTranslatable()) {
            if (!is_array($this->title)) {
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
        return ['title' => $this->title];
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return null === $this->title || is_array($this->title) && 0 === count(array_filter($this->title));
    }
}
