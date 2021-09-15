<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query;

use Proximum\Vimeet\Application\ThirdParty\LENI\LeniConstants;

/**
 * Get array of fields to request to the Get API of LENI
 */
class FieldsByEventQueryHandler
{
    public function handle(FieldsByEventQuery $fieldsByEventQuery): array
    {
        return array_values(
            array_unique(
                array_merge(
                    LeniConstants::LENI_GET_FIELDS,
                    $this->getFieldsFromTypesMapping($fieldsByEventQuery->typesMapping),
                    $fieldsByEventQuery->customDataMapping[LeniConstants::DATA_MAPPING_FORMAT_TAGS]
                )
            )
        );
    }

    private function getFieldsFromTypesMapping(array $typesMapping): array
    {
        $fields = [];

        foreach ($typesMapping as $typeMapping) {
            if (\is_array($typeMapping)) {
                foreach ($typeMapping as $fieldName => $value) {
                    $fields[$fieldName] = true;
                }
            }
        }

        return array_keys($fields);
    }
}
