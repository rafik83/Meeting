<?php

namespace Proximum\Vimeet\Application\View\Tip\Event;

use Proximum\Vimeet\Application\View\Tip\AbstractTipView;
use Proximum\Vimeet\Domain\Model\Type;

class TipView extends AbstractTipView
{
    /** @var string */
    public $locale;

    /** @var Type[] */
    public $types;

    /** @var array */
    public $pages;

    /**
     * TipView constructor.
     *
     * @param int    $id
     * @param string $title
     * @param string $locale
     * @param Type[] $types
     * @param array  $pages
     */
    public function __construct($id, $title, $locale, array $types = [], array $pages = [])
    {
        parent::__construct($id, $title);

        $this->locale = $locale;
        $this->types  = $types;
        $this->pages  = $pages;
    }
}
