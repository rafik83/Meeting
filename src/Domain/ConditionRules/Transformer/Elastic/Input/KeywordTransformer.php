<?php


namespace Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input;

use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Search\ContentQueryBuilderInterface;

/**
 * This class is used to search of keyword on the sheets.
 * It creates a query on the sheet name, the content and the content localized
 * based on the equivalent request made by the Catalog Search.
 */
class KeywordTransformer implements InputTransformerInterface
{
    /**
     * @var ContentQueryBuilderInterface
     */
    private $contentQueryBuilder;

    public function __construct(ContentQueryBuilderInterface $contentQueryBuilder)
    {
        $this->contentQueryBuilder = $contentQueryBuilder;
    }

    public function transform(Field $field): array
    {
        if (!$this->supports($field)) {
            return [];
        }

        return $this->contentQueryBuilder->getQuery($field->getValue(), $field->getLocale());
    }

    public function supports(Field $field): bool
    {
        return 'keywords' === $field->getField();
    }
}
