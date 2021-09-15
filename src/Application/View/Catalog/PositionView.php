<?php

namespace Proximum\Vimeet\Application\View\Catalog;

class PositionView implements TagViewInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $title;

    /**
     * PositionView constructor.
     *
     * @param string $key
     * @param string $title
     */
    public function __construct($key, $title)
    {
        $this->key   = $key;
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
