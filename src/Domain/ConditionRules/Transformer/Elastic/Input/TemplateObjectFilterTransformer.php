<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class TemplateObjectFilterTransformer implements InputTransformerInterface
{
    public function transform(Field $field): array
    {
        return [];
    }

    public function supports(Field $field): bool
    {
        return false !== stripos($field->getField(), TypesMapping::USER_EVENT_VIEW_TEMPLATE_OBJECT_FILTERS);
    }
}
