<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\View\Sheet\Catalog\CatalogSheetPreviewExternalView;

class SheetPreviewExternalViewQueryHandler
{
    /**
     * @var Preview
     */
    private $preview;

    /**
     * SheetPreviewExternalViewQueryHandler constructor.
     *
     * @param Preview $preview
     */
    public function __construct(Preview $preview)
    {
        $this->preview = $preview;
    }

    /**
     * @param SheetPreviewExternalViewQuery $query
     *
     * @return CatalogSheetPreviewExternalView
     */
    public function handle(SheetPreviewExternalViewQuery $query)
    {
        return new CatalogSheetPreviewExternalView(
            $query->sheet->getId(),
            $query->sheet->getTitle(),
            $query->sheet->getType()->getTitle($query->locale),
            $this->preview->getPreview($query->sheet, $query->locale),
            $query->sheet
        );
    }
}
