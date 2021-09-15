<?php

namespace Proximum\Vimeet\Application\View\Navigation;

class LinkView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $link;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var bool
     */
    public $state;

    /**
     * @var StateButtonView
     */
    public $button;

    /**
     * LinkView constructor.
     *
     * @param string               $label
     * @param null|string          $link
     * @param null|string          $locale
     * @param null|StateButtonView $button
     * @param bool                 $state
     */
    public function __construct($label, $link = null, $locale = null, StateButtonView $button = null, $state = true)
    {
        $this->label  = $label;
        $this->link   = $link;
        $this->locale = $locale;
        $this->button = $button;

        if (null === $link) {
            $this->state = false;
        } else {
            $this->state = $state;
        }
    }
}
