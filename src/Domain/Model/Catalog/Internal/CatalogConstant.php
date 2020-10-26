<?php

namespace Proximum\Vimeet\Domain\Model\Catalog\Internal;

final class CatalogConstant
{
    public const AVAILABLE_SLOT_IDS_FILTER_EVERYONE = 'catalog_everyone';
    public const AVAILABLE_SLOT_IDS_FILTER_AVAILABLE = 'catalog_available';
    public const AVAILABLE_SLOT_IDS_FILTER_SLOT = 'catalog_slot';

    public const FILTER_SHEET_SAW = 'sheetSaw';
    public const FILTER_VIEWED_BY_SHEET = 'viewedBySheet';

    public static function getAllSheetVisitChoices(): array
    {
        return [
            self::FILTER_SHEET_SAW,
            self::FILTER_VIEWED_BY_SHEET,
        ];
    }
}
