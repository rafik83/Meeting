<?php

namespace Proximum\Vimeet\Tests\Domain\ConditionRules;

use Proximum\Vimeet\Domain\Catalog\TaggedNomenclatureFilterGetter;
use Proximum\Vimeet\Domain\Catalog\View\NomenclatureFilterView;
use Proximum\Vimeet\Domain\ConditionRules\GetTagsByNomenclature;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\ConditionRules\View\ComparisonOperator\ComparisonOperatorIn;
use Proximum\Vimeet\Domain\ConditionRules\View\Field;
use Proximum\Vimeet\Domain\Model\Event;

class GetTagsByNomenclatureTest extends TestCase
{
    public function test__invoke()
    {
        $field = new Field('nestedTaggedData.1', new ComparisonOperatorIn(), 'checkbox', ['57eced1b99305', '57eced1b994ef'], 'fr');
        $event = $this->prophesize(Event::class);
        $nomenclatureFilterViews = [
            1 => new NomenclatureFilterView(1, 'mbappe', ['u58b57c0ecbdb3' => 'dribble'], [0 => 'tag', 1 => 'tag2']),
        ];
        $taggedNomenclatureFilterGetter = $this->prophesize(TaggedNomenclatureFilterGetter::class);
        $taggedNomenclatureFilterGetter
            ->getNomenclaturesItemsByEvent($event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($nomenclatureFilterViews);

        $getTagsByNomenclature = new GetTagsByNomenclature($taggedNomenclatureFilterGetter->reveal());

        $result = $getTagsByNomenclature($field, $event->reveal(), 'fr');
        $this->assertSame($result, ['tag', 'tag2']);
    }
}
