<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter;

class CustomDataConverter
{
    /**
     * @param array $customDataMapping LENI field indexed by tag,
     *                                 example: ['sheet_state' => 'ZL_MODERATION', 'sheet_organization_staff' => 'ZL_Effectif', 'sheet_generic_tag_20' => 'ZL_TypePrestation']
     * @param array $rawUser           LENI data indexed by LENI fiedName,
     *                                 example: ['ZL_MODERATION' => 'W', 'ZL_Effectif' => 'A1', 'ZL_TypePrestation' => ['P12', 'P3', 'P5']]
     *
     * @return array indexed by tag
     *    exemple: [
     *         'states' => ['sheet_state' => 'W'],
     *         'tags' => ['sheet_organization_staff' => 'A1', 'sheet_generic_tag_20' => ['P12', 'P3', 'P5']],
     *  ]
     */
    public function convert(array $customDataMapping, array $rawUser): array
    {
        $dataIndexedByTag = [];

        foreach ($customDataMapping as $type => $fields) {
            if (!is_array($fields)) {
                continue;
            }

            foreach ($fields as $tag => $fieldName) {
                if (isset($rawUser[$fieldName])) {
                    $dataIndexedByTag[$type][$tag] = $rawUser[$fieldName];
                }
            }
        }

        return $dataIndexedByTag;
    }
}
