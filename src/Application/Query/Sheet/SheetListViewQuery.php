<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Admin;

class SheetListViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Admin
     */
    public $admin;

    /**
     * SheetListViewQuery constructor.
     *
     * @param Sheet  $sheet
     * @param string $locale
     * @param Admin  $admin
     */
    public function __construct(Sheet $sheet, $locale, Admin $admin)
    {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->admin  = $admin;
    }
}
