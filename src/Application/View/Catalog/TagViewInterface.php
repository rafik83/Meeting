<?php

namespace Proximum\Vimeet\Application\View\Catalog;

interface TagViewInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @return string
     */
    public function getTitle();
}
