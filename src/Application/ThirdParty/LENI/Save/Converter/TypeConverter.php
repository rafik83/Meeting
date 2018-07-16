<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Save\Converter;

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

        return [];
    }
}
