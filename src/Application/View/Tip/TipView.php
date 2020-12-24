<?php

namespace Proximum\Vimeet\Application\View\Tip;

class TipView extends AbstractTipView
{
    /** @var array */
    public $pagesTranslations;

    /**
     * TipView constructor.
     *
     * @param int    $id
     * @param string $title
     * @param array  $pagesTranslations
     */
    public function __construct($id, $title, array $pagesTranslations)
    {
        parent::__construct($id, $title);

        $this->pagesTranslations = $pagesTranslations;
    }
}
