<?php

namespace Proximum\Vimeet\Application\View\Navigation;

class SubmenuView
{
    /** Isolate $customButtonViews to display on the mobile. $customButtonViews objects are present in $buttonsViews */

    /**
     * @var SubmenuButtonView[]
     */
    public $submenuButtonViews;

    /**
     * @var SubmenuButtonView[]
     */
    public $customButtonViews;

    /**
     * SubmenuView constructor.
     *
     * @param SubmenuButtonView[] $submenuButtonViews
     * @param SubmenuButtonView[] $customButtonViews
     */
    public function __construct(array $submenuButtonViews, array $customButtonViews = [])
    {
        $this->submenuButtonViews = $submenuButtonViews;
        $this->customButtonViews = $customButtonViews;
    }
}
