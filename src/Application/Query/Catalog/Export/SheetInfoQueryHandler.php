<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Catalog\Export;

class SheetInfoQueryHandler
{
    /** @var array of sheet fields key => label */
    private $sheetFields = [];

    /**
     * @param SheetInfoQuery $query
     *
     * @return array of object key => content
     */
    public function handle(SheetInfoQuery $query): array
    {
        $data = [];

        foreach ($query->templateData->getExportableObjects() as $object) {
            $key = $object->getKey();
            $fieldName = $object->getExportableFieldname($query->locale, $query->fallback);

            if (!isset($this->sheetFields[$key])) {
                $this->sheetFields[$key] = $fieldName;
            }

            $data[$key] = $object->getExportableContent($query->taggedData, $query->locale);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getSheetFields(): array
    {
        return $this->sheetFields;
    }
}
