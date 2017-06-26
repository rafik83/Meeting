<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\View\Sheet\Catalog\CatalogSheetPreviewLogoutView;

class SheetPreviewLogoutViewQueryHandler
{
    /**
     * @var Preview
     */
    private $preview;

    /**
     * SheetPreviewLogoutViewQueryHandler constructor.
     *
     * @param Preview $preview
     */
    public function __construct(Preview $preview)
    {
        $this->preview = $preview;
    }

    /**
     * @param SheetPreviewLogoutViewQuery $query
     *
     * @return CatalogSheetPreviewLogoutView
     */
    public function handle(SheetPreviewLogoutViewQuery $query)
    {
        return new CatalogSheetPreviewLogoutView(
            $query->sheet->getId(),
            $query->sheet->getTitle(),
            $query->sheet->getType()->getTitle($query->locale),
            $this->preview->getPreview($query->sheet, $query->locale),
            $query->sheet
        );
    }
}
