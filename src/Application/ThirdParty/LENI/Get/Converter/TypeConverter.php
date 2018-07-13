<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter;

use Proximum\Vimeet\Domain\Model\Type;

class TypeConverter
{
    /**
     * @param Type[] $types
     * @param array  $mapping
     * @param array  $payload
     *
     * @return null|Type
     */
    public function convert(array $types, array $mapping, array $payload): ?Type
    {
        $typeId = $this->getTypeId($mapping, $payload);

        if (null === $typeId) {
            return null;
        }

        foreach ($types as $type) {
            if ($type->getId() === $typeId) {
                return $type;
            }
        }

        return null;
    }

    /**
     * @param array $mapping
     * @param array $payload
     *
     * @return int|null
     */
    private function getTypeId(array $mapping, array $payload): ?int
    {
        foreach ($mapping as $typeId => $mappedType) {
            if ($this->matchType($mappedType, $payload)) {
                return (int) $typeId;
            }
        }

        return null;
    }

    /**
     * @param array $mappedType
     * @param array $payload
     *
     * @return bool
     */
    private function matchType(array $mappedType, array $payload): bool
    {
        foreach ($mappedType as $fieldName => $fieldValue) {
            if (!array_key_exists($fieldName, $payload)) {
                return false;
            }

            if (\is_array($payload[$fieldName])) {
               if (!\in_array((string) $fieldValue, $payload[$fieldName], true)) {
                   return false;
               }

               continue;
            }

            if ((string) $fieldValue !== (string) $payload[$fieldName]) {
                return false;
            }
        }

        return true;
    }
}
