<?php

namespace Proximum\Vimeet\Domain\UserEventView;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;

class TemplateObjectFilterTransformer
{
    public static function transform(array $templateFilters, array $formData): array
    {
        $dataMappedToTemplateFilters = [];

        foreach ($formData as $templateKey => $data) {
            if (isset($templateFilters[$templateKey])) {
                $value = null;
                $type = null;

                if (isset($data['boolean'])) {
                    $value = $data['boolean'];
                    $type = 'boolean';
                }

                if (isset($data['path'])) {
                    $value = $data['path'];
                    $type = 'path';
                }

                $dataMappedToTemplateFilters[] = [
                    TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_TYPE => $type,
                    TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_VALUE => $value,
                    TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_KEY => $templateKey
                ];
            }
        }

        return $dataMappedToTemplateFilters;
    }
}
