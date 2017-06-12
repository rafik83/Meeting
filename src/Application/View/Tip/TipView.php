<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
