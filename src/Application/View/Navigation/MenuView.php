<?php

namespace Proximum\Vimeet\Application\View\Navigation;

class MenuView
{
    /**
     * @var CategoryView[]
     */
    public $categoriesView;

    /**
     * MenuView constructor.
     *
     * @param CategoryView[] $categoriesView
     */
    public function __construct(array $categoriesView)
    {
        $this->categoriesView = $categoriesView;
    }
}
