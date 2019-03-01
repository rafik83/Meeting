<?php

namespace Proximum\Vimeet\Domain\UserEventView;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;
use Proximum\Vimeet\Domain\Model\Filter\FilledTemplateFilter;

class TemplateObjectFilterTransformer
{
    public static function transform(array $templateFilters, array $formData): array
    {
        $dataMappedToTemplateFilters = [];

        foreach ($templateFilters as $templateKey => $templateFilter) {
            $value = 'none';
            $type = 'none';

            if ($templateFilter instanceof BooleanTemplateFilter) {
                $type = 'boolean';

                if (isset($formData[$templateKey])) {
                    $value = $formData[$templateKey]['boolean'] ?? 'none';
                }
            }

            if ($templateFilter instanceof FilledTemplateFilter) {
                $type = 'upload';

                if (isset($formData[$templateKey])) {
                    $value = $formData[$templateKey]['path'] ?? 'none';
                }
            }

            $dataMappedToTemplateFilters[] = [
                TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_TYPE => $type,
                TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_VALUE => $value,
                TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS_KEY => $templateKey
            ];
        }

        return $dataMappedToTemplateFilters;
    }
}
