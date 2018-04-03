<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter;

class CustomDataConverter
{
    /**
     * @param array $customDataMapping LENI field indexed by tag,
     *              example: ['sheet_state' => 'ZL_MODERATION', 'sheet_organization_staff' => 'ZL_Effectif', 'sheet_generic_tag_20' => 'ZL_TypePrestation']
     * @param array $taggedData data indexed by tag,
     *              example: ['sheet_state' => 'Y', 'sheet_organization_staff' => 'A1', 'sheet_generic_tag_20' => ['P12', 'P3', 'P5']]
     *
     * @return array indexed by LENI field
     *               exemple: ['ZL_MODERATION' => 'Y', 'ZL_Effectif' => 'A1', 'ZL_TypePrestation' => ['P12', 'P3', 'P5']]
     */
    public function convert(array $customDataMapping, array $taggedData): array
    {
        $dataIndexedByFieldName = [];

        foreach ($customDataMapping as $tag => $fieldName) {
            if (isset($taggedData[$tag])) {
                $dataIndexedByFieldName[$fieldName] = $taggedData[$tag];
            }
        }

        return $dataIndexedByFieldName;
    }
}
