<?php


namespace Proximum\Vimeet\Tests\Domain\ConditionRules\Transformer\Elastic\Input;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\KeywordTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorContains;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Infrastructure\Elastica\QueryBuilder\ContentQueryBuilder;

class KeywordTransformerTest extends TestCase
{
    public function testTransform(): void
    {
        $field = new Field('keywords', new ComparisonOperatorContains(), 'select', 'R&D', 'fr');

        $contentQueryBuilder = new ContentQueryBuilder();
        $keywordTransformer = new KeywordTransformer(
            $contentQueryBuilder
        );

        $expectedResult = [
            'multi_match' => [
                'minimum_should_match' => '90%',
                'fields' => [
                    'sheetName^5',
                    'content^2',
                    'content_fr^3'
                ],
                'type' => 'cross_fields',
                'query' => 'R&D',
            ]
        ];

        $this->assertSame($keywordTransformer->transform($field), $expectedResult);
    }
}
