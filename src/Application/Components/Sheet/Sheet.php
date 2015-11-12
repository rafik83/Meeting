<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet as SheetModel;
use Proximum\Vimeet\Domain\Model\SheetDataView;
use Proximum\Vimeet\Domain\Repository\NomenclatureItemRepositoryInterface;

class Sheet
{
    /**
     * @var NomenclatureItemRepositoryInterface
     */
    private $nomenclatureItemRepository;

    /**
     * @param NomenclatureItemRepositoryInterface $nomenclatureItemRepository
     */
    public function __construct(NomenclatureItemRepositoryInterface $nomenclatureItemRepository)
    {
        $this->nomenclatureItemRepository = $nomenclatureItemRepository;
    }

    /**
     * @param SheetModel $sheet
     * @param string     $locale
     *
     * @return SheetView
     */
    public function getSheetDataView(SheetModel $sheet, $locale)
    {
        return new SheetDataView(
            $sheet->getId(),
            $sheet->getEvent(),
            $sheet->getType(),
            $sheet->getParticipants(),
            $this->getData($sheet, $locale),
            $sheet->getPackageData(),
            $sheet->getBillingData()
        );
    }

    /**
     * @param SheetModel $sheet
     * @param string     $locale
     *
     * @return mixed
     */
    private function getData(SheetModel $sheet, $locale)
    {
        $sheetTemplate = $sheet->getTypeSheetTemplate();
        $data          = $sheet->getData();

        foreach ($sheetTemplate as $keyBlock => $block) {
            if (isset($block['template'])) {
                foreach ($block['template'] as $keyField => $field) {
                    if (isset($field['type'])
                        && 'lib_nomenclature' === $field['type']
                        && isset($data[$keyBlock][$keyField]['value'])
                    ) {
                        $data[$keyBlock][$keyField]['label'] = $this
                            ->nomenclatureItemRepository
                            ->getNomenclatureItemLabelById($data[$keyBlock][$keyField]['value'], $locale);
                    }
                }
            }
        }

        return $data;
    }
}
