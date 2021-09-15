<?php


namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Application\Adapter\ElasticSearch\TypesMapping;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorNotIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Model\Event;

class MessageTransformer implements InputTransformerInterface
{
    /** @var Event */
    private $event;

    /** @var string */
    private $locale;

    public function setEventAndLocale(Event $event, string $locale): void
    {
        $this->event = $event;
        $this->locale = $locale;
    }

    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        $messageIds = $field->getValue();

        $query = $this->buildNestedMessageIdsQuery($messageIds);

        if ($this->isContraryComparisonOperator($field)) {
            return [
                'bool' => [
                    'must_not' => $query,
                ],
            ];
        }

        return $query;
    }

    private function buildNestedMessageIdsQuery(array $messageIds): array
    {
        $query = [];

        foreach ($messageIds as $messageId) {
            $query['bool']['should'][] = [
                'match' => [
                    sprintf('%s.id', TypesMapping::SHEET_MESSAGES_RECEIVED) => $messageId,
                ],
            ];
        }

        return [
            'nested' => [
                'path'  => TypesMapping::SHEET_MESSAGES_RECEIVED,
                'query' => $query,
            ],
        ];
    }

    public function supports(Field $field): bool
    {
        return 'messagesReceived' === $field->getField();
    }

    private function isContraryComparisonOperator(Field $field): bool
    {
        return $field->getComparisonOperator() instanceof ComparisonOperatorNotIn;
    }
}
