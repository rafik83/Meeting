<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter;

/**
 * Merge all LENI to Vimeet data indexed by tag with main and custom data converters
 */
class DataConverter
{
    /** @var MainDataConverter */
    private $mainDataConverter;

    /** @var CustomDataConverter */
    private $customDataConverter;

    public function __construct(MainDataConverter $mainDataConverter, CustomDataConverter $customDataConverter)
    {
        $this->mainDataConverter = $mainDataConverter;
        $this->customDataConverter = $customDataConverter;
    }

    /**
     * @param array $customDataMapping LENI field indexed by tag,
     *                                 example: ['sheet_organization_staff' => 'ZL_Effectif', 'sheet_generic_tag_20' => 'ZL_ACTIVITE']
     * @param array $rawUser           LENI data indexed by LENI fiedName,
     *                                 example: ['ZL_Effectif': 'A1', 'ZL_TypePrestation': ['P12', 'P3', 'P5']
     *
     * @return array indexed by tag
     */
    public function convert(array $customDataMapping, array $rawUser): array
    {
        $dataIndexedByTag = $this->mainDataConverter->convert($rawUser);
        $customData = $this->customDataConverter->convert($customDataMapping, $rawUser);

        foreach ($customData as $type => $data) {
            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $tag => $value) {
                $dataIndexedByTag[$tag] = $value;
            }
        }

        return $dataIndexedByTag;
    }
}
