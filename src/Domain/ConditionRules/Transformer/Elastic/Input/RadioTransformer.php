<?php

namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\QueryKeyTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class RadioTransformer implements InputTransformerInterface
{
    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        return [
            'term' => [
                QueryKeyTransformer::getQueryKey($field) => $field->getValue(),
            ],
        ];
    }

    public function supports(Field $field): bool
    {
        return 'radio' === $field->getInput();
    }
}
