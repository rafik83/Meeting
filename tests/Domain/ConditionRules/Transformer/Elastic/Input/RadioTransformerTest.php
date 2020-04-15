<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
        $field = new Field('isVisio', new ComparisonOperatorEqual(), 'radio', 'yes', 'FR');
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
        $field = new Field('isVisio', new ComparisonOperatorEqual(), 'radio', 'yes', 'FR');
        $this->assertTrue($radioTransformer->supports($field));
    }
}
