<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Nomenclature\ItemsToCheckboxTransformer;

class ItemsToCheckboxTransformerTest extends TestCase
{
    public function testTransform()
    {
        $nomenclature = new Nomenclature('test', 1, [
            'a' => ['label' => ['fr' => 'aaa']],
            'b' => ['label' => ['fr' => 'bbb']],
            'c' => ['label' => ['fr' => 'ccc']],
            'd' => ['label' => ['fr' => 'ddd']],
            'e' => ['label' => ['fr' => 'eee']],
            'f' => ['label' => ['fr' => 'fff']],
        ]);

        $transformer = new ItemsToCheckboxTransformer($nomenclature);

        $expected = [
            new NomenclatureItem('a', ['fr' => 'aaa'], []),
            new NomenclatureItem('d', ['fr' => 'ddd'], []),
            new NomenclatureItem('e', ['fr' => 'eee'], []),
        ];

        $this->assertEquals($expected, $transformer->transform(['a', 'd', 'e']));
    }

    public function testReverseTransform()
    {
        $nomenclature = new Nomenclature('test', 1, [
            'a' => ['label' => ['fr' => 'aaa']],
            'b' => ['label' => ['fr' => 'bbb']],
            'c' => ['label' => ['fr' => 'ccc']],
            'd' => ['label' => ['fr' => 'ddd']],
            'e' => ['label' => ['fr' => 'eee']],
            'f' => ['label' => ['fr' => 'fff']],
        ]);

        $transformer = new ItemsToCheckboxTransformer($nomenclature);

        $items = [
            new NomenclatureItem('a', ['fr' => 'aaa'], []),
            new NomenclatureItem('d', ['fr' => 'ddd'], []),
            new NomenclatureItem('e', ['fr' => 'eee'], []),
        ];

        $this->assertEquals(['a', 'd', 'e'], $transformer->reverseTransform($items));
    }

    public function testReverseTransformTransform()
    {
        $nomenclature = new Nomenclature('test', 1, [
            'a' => ['label' => ['fr' => 'aaa']],
            'b' => ['label' => ['fr' => 'bbb']],
            'c' => ['label' => ['fr' => 'ccc']],
            'd' => ['label' => ['fr' => 'ddd']],
            'e' => ['label' => ['fr' => 'eee']],
            'f' => ['label' => ['fr' => 'fff']],
        ]);

        $transformer = new ItemsToCheckboxTransformer($nomenclature);

        $this->assertEquals(['a', 'd', 'e'], $transformer->reverseTransform($transformer->transform(['a', 'd', 'e'])));
    }
}
