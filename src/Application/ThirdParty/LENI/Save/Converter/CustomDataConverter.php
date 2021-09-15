<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;

class CustomDataConverter
{
    /**
     * @param array $customDataMapping LENI field indexed by tag,
     *  example: [
     *   'states' => ['sheet_state' => 'ZL_MODERATION']
     *   'tags' => ['sheet_organization_staff' => 'ZL_Effectif', 'sheet_generic_tag_20' => 'ZL_TypePrestation']
     *  ]
     * @param array $taggedData        data indexed by tag,
     * example: [
     *  'states' => ['sheet_state' => 'Y'],
     *  'tags' => ['sheet_organization_staff' => 'A1', 'sheet_generic_tag_20' => ['P12', 'P3', 'P5']]
     * ]
     *
     * @return array indexed by LENI field
     *               exemple: ['ZL_MODERATION' => 'Y', 'ZL_Effectif' => 'A1', 'ZL_TypePrestation' => ['P12', 'P3', 'P5']]
     */
    public function convert(array $customDataMapping, array $taggedData): array
    {
        $dataIndexedByFieldName = [];

        foreach ($customDataMapping as $type => $fields) {
            if (!is_array($fields)) {
                continue;
            }

            foreach ($fields as $identifier => $fieldName) {
                if ($type === LeniConstants::DATA_MAPPING_FORMAT_PRODUCTS) {
                    $dataIndexedByFieldName[$fieldName] =
                        isset($taggedData[$type][$identifier])
                            ? LeniConstants::LENI_MAPPING_BOOLEAN_TRUE
                            : LeniConstants::LENI_MAPPING_BOOLEAN_FALSE
                    ;
                } elseif (isset($taggedData[$type][$identifier])) {
                    $dataIndexedByFieldName[$fieldName] = $taggedData[$type][$identifier];
                }
            }
        }

        return $dataIndexedByFieldName;
    }
}
