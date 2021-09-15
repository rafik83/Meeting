<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter\RawNomenclatureToNomenclatureViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\ComexposiumWebservice;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\PrepareNomenclatureHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureView;

class PrepareNomenclatureHandlerTest extends TestCase
{
    public function testHandle()
    {
        $nomenclature1 = new \stdClass();
        $nomenclature1->reference = '2233';

        $nomenclature2 = new \stdClass();
        $nomenclature2->reference = '6666';

        $comexposiumWebservice = $this->prophesize(ComexposiumWebservice::class);
        $comexposiumWebservice
            ->getEventNomenclatures('1337')
            ->shouldBeCalled()
            ->willReturn([$nomenclature1, $nomenclature2])
        ;

        $rawNomenclatureToNomenclatureViewConverter = $this->prophesize(
            RawNomenclatureToNomenclatureViewConverter::class
        );
        $rawNomenclatureToNomenclatureViewConverter
            ->convert($nomenclature1)
            ->shouldBeCalled()
            ->willReturn(new NomenclatureItemView('2233', '6666', []))
        ;
        $rawNomenclatureToNomenclatureViewConverter
            ->convert($nomenclature2)
            ->shouldBeCalled()
            ->willReturn(new NomenclatureItemView('6666', null, []))
        ;

        $prepareNomenclatureHandler = new PrepareNomenclatureHandler(
            $comexposiumWebservice->reveal(),
            $rawNomenclatureToNomenclatureViewConverter->reveal()
        );
        $result = $prepareNomenclatureHandler->handle('1337');

        $parentNomenclatureItem = new NomenclatureItemView('6666', null, []);
        $childNomenclatureItem = new NomenclatureItemView('2233', '6666', []);
        $childNomenclatureItem->setParentNomenclatureItemView($parentNomenclatureItem);

        $expectedResult = new NomenclatureView([
            '6666' => $parentNomenclatureItem,
            '2233' => $childNomenclatureItem,
        ]);

        $this->assertEquals($expectedResult, $result);
    }
}
