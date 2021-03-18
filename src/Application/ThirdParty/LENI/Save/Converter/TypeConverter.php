<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter;

use Proximum\Vimeet\Application\ThirdParty\LENI\Exception\TypeDoesNotMatchException;
use Proximum\Vimeet\Domain\Model\Type;

/**
 * This class is used to convert a domain Domain/Model/Type to CategoryIndividuEvt for LENI api Call
 */
class TypeConverter
{
    /**
     * @param Type  $type
     * @param array $mapping
     *
     * @throws TypeDoesNotMatchException
     *
     * @return array
     */
    public function convert(Type $type, array $mapping): array
    {
        foreach ($mapping as $typeId => $mappedType) {
            if ((int) $typeId === $type->getId()) {
                $typeMapping = [];

                if (!\is_array($mappedType)) {
                    return [];
                }

                if (isset($mappedType['condition'])) {
                    return [];
                }

                foreach ($mappedType as $mappedKey => $mappedField) {
                    $typeMapping[$mappedKey] = $mappedField;
                }

                return $typeMapping;
            }
        }

        throw new TypeDoesNotMatchException();
    }
}
