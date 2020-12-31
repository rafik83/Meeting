<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\View\Sheet\Catalog\CatalogSheetPreviewExternalView;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetPreviewExternalViewQueryHandler
{
    /** @var Preview */
    private $preview;

    /**
     * @param Preview $preview
     */
    public function __construct(Preview $preview)
    {
        $this->preview = $preview;
    }

    public function handle(SheetPreviewExternalViewQuery $query): CatalogSheetPreviewExternalView
    {
        return new CatalogSheetPreviewExternalView(
            $query->sheet->getId(),
            $query->sheet->getTitle(),
            $this->getTypeOrCategoryTitle($query->showCategory, $query->sheet, $query->locale),
            $this->preview->getPreview($query->sheet, $query->locale),
            $query->sheet
        );
    }

    private function getTypeOrCategoryTitle(bool $showCategory, Sheet $sheet, string $locale): string
    {
        if (false === $showCategory) {
            return $sheet->getType()->getTitle($locale);
        }

        return implode(', ', $sheet->getType()->getCategoriesTitles($locale));
    }
}
