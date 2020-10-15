<?php

namespace Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Configuration;

class MappingConfigurationChecker
{
    public function isMappingConfigurationValid($mapping): bool
    {
        if (!is_array($mapping)) {
            return false;
        }

        // Check that required field are present.
        if (!isset($mapping['endpoint'], $mapping['mandatory_keys'], $mapping['types'])) {
            return false;
        }

        // Check that there is email and identifier defined.
        if (!isset($mapping['mandatory_keys']['email'], $mapping['mandatory_keys']['identifier'])) {
            return false;
        }

        // Check that each type as an id, a condition and a mapping
        foreach ($mapping['types'] as $typeId => $typeConfig) {
            if ($typeId === null) {
                return false;
            }

            if (!isset($typeConfig['condition'], $typeConfig['mapping'])) {
                return false;
            }
        }

        return true;
    }
}
