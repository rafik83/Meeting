<?php

namespace Proximum\Vimeet\Tests\Application\Components\Sheet\Nomenclature;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Sheet\Nomenclature\NomenclatureItemsGetter;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class NomenclatureItemsGetterTest extends TestCase
{
    public function testGetNomenclatureItems()
    {
        $sheet = $this->prophesize(Sheet::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $object1 = $this->prophesize(Nomenclature::class);
        $object2 = $this->prophesize(Nomenclature::class);
        $object3 = $this->prophesize(Nomenclature::class);
        $object1->getObjective()->willReturn('none');
        $object2->getObjective()->willReturn('needs');
        $object3->getObjective()->willReturn('supply');
        $object1->getData()->willReturn([
            'items' => ['57f257fc436e4', '57f257fc43c70'],
        ]);
        $object2->getData()->willReturn([
            'items' => ['57fce88291663', '57fce88292bbe'],
        ]);
        $object3->getData()->willReturn([
            'items' => ['57fcf1ed417b3'],
        ]);
        $templateData = $this->prophesize(TemplateData::class);
        $templateData
            ->getNomenclatureObjects()
            ->shouldBeCalled()
            ->willReturn([
                $object1->reveal(),
                $object2->reveal(),
                $object3->reveal(),
            ])
        ;

        $templateDataFactory->createFromSheet($sheet->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $nomenclatureItemsGetter = new NomenclatureItemsGetter($templateDataFactory->reveal());

        $result = $nomenclatureItemsGetter->getNomenclatureItems($sheet->reveal(), 'fr');

        $expected = [
            'none'   => ['57f257fc436e4', '57f257fc43c70'],
            'supply' => ['57fcf1ed417b3'],
            'needs'  => ['57fce88291663', '57fce88292bbe'],
        ];

        $this->assertEquals($expected, $result);
    }
}
