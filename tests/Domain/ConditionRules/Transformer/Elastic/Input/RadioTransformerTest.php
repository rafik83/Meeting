<?php

namespace Proximum\Vimeet\Tests\Domain\ConditionRules\Transformer\Elastic\Input;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\Transformer\Elastic\Input\RadioTransformer;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorEqual;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;

class RadioTransformerTest extends TestCase
{
    public function testTransform()
    {
        $radioTransformer = new RadioTransformer();
        $field = new Field('isVisio', new ComparisonOperatorEqual(), 'radio', 'yes', 'fr');
        $this->assertEquals(
            [
                'term' => [
                    'isVisio' => 'yes',
                ],
            ],
            $radioTransformer->transform($field)
        );
    }

    public function testSupports()
    {
        $radioTransformer = new RadioTransformer();
        $field = new Field('isVisio', new ComparisonOperatorEqual(), 'radio', 'yes', 'fr');
        $this->assertTrue($radioTransformer->supports($field));
    }
}
