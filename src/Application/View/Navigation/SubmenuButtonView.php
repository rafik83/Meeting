<?php

namespace Proximum\Vimeet\Application\View\Navigation;

class SubmenuButtonView
{
    /** @var string */
    public $icon;

    /** @var int|null */
    private $counter;

    /** @var bool */
    public $state;

    /** @var string */
    public $label;

    /** @var string */
    public $link;

    /** @var bool */
    public $isShowOnMobile;

    /** @var array */
    public $attributes;

    /**
     * SubmenuButtonView constructor.
     *
     * @param string|null $icon
     * @param string|null $label
     * @param string|null $link
     * @param bool        $state
     * @param int|null    $counter
     * @param bool        $isShowOnMobile
     * @param array|null  $attributes
     */
    public function __construct(
        ?string $icon,
        ?string $label,
        ?string $link,
        bool $state = true,
        ?int $counter = null,
        bool $isShowOnMobile = false,
        array $attributes = []
    ) {
        $this->icon = $icon;
        $this->state = $state;
        $this->label = $label;
        $this->link = $link;
        $this->counter = $counter;
        $this->isShowOnMobile = $isShowOnMobile;
        $this->attributes = $attributes;
    }

    public function getCounter(): ?int
    {
        return $this->counter;
    }
}
