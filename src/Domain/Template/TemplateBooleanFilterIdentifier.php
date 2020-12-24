<?php

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Template\TemplateObject\BooleanObject;

class TemplateBooleanFilterIdentifier
{
    /**
     * @param TemplateData $templateData
     *
     * @return array
     */
    public static function getBooleanFilterValues(TemplateData $templateData)
    {
        $filters = [];

        foreach ($templateData->getObjects() as $object) {
            if ($object instanceof BooleanObject
                && $object->isFilter()
                && null !== $object->getBoolean()
                && false !== $object->getBoolean()
            ) {
                $filters[] = [
                    'key' => $object->getKey(),
                ];
            }
        }

        return $filters;
    }

    /**
     * @param TemplateData $templateData
     *
     * @return array
     */
    public static function getBooleanFilterLabel(TemplateData $templateData)
    {
        $filters = [];

        foreach ($templateData->getObjects() as $object) {
            if ($object instanceof BooleanObject
                && $object->isFilter()
            ) {
                $filters[] = [
                    'key' => $object->getKey(),
                    'value' => $object->getFilterLabel(),
                    'tags' => $object->getTags(),
                ];
            }
        }

        return $filters;
    }

    /**
     * @param array $templateDatas
     *
     * @return array
     */
    public static function getBooleanFilters(array $templateDatas)
    {
        $filters = [];

        foreach ($templateDatas as $templateData) {
            $filters = array_merge($filters, self::getBooleanFilterLabel($templateData));
        }

        return $filters;
    }
}
