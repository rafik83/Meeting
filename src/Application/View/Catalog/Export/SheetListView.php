<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog\Export;

class SheetListView
{
    /** @var array */
    public $sheetViews;

    /** @var array */
    public $registrationFields;

    /** @var array */
    public $sheetFields;

    /** @var string */
    public $typeOrCategoryColumn;

    /** @var bool */
    public $isTypeColumn;

    /**
     * @param array  $sheetViews
     * @param array  $registrationFields
     * @param array  $sheetFields
     * @param string $typeOrCategoryColumn
     * @param bool   $isTypeColumn
     */
    public function __construct(
        array $sheetViews = [],
        array $registrationFields = [],
        array $sheetFields = [],
        string $typeOrCategoryColumn,
        bool $isTypeColumn = true
    ) {
        $this->sheetViews = $sheetViews;
        $this->registrationFields = $registrationFields;
        $this->sheetFields = $sheetFields;
        $this->typeOrCategoryColumn = $typeOrCategoryColumn;
        $this->isTypeColumn = $isTypeColumn;
    }
}
