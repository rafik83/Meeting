<?php

namespace Proximum\Vimeet\Application\View\Navigation;

class CategoryView
{
    /** @var string */
    public $title;

    /** @var string */
    public $icon;

    /** @var LinkView[] */
    public $linksView;

    /** @var bool */
    public $isShowOnMobile;

    /** @var bool */
    public $isExpandedOnMobile;

    public function __construct(
        string $title,
        string $icon,
        array $linksView,
        bool $isShowOnMobile,
        bool $isExpandedOnMobile = false
    ) {
        $this->title = $title;
        $this->icon = $icon;
        $this->linksView = $linksView;
        $this->isShowOnMobile = $isShowOnMobile;
        $this->isExpandedOnMobile = $isExpandedOnMobile;
    }

    public function getEnabledLinkView(): ?LinkView
    {
        foreach ($this->linksView as $linkView) {
            if (true === $linkView->state) {
                return $linkView;
            }
        }

        return null;
    }
}
