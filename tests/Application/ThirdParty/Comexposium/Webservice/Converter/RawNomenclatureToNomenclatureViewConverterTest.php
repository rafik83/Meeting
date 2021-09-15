<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter\RawNomenclatureToNomenclatureViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemTranslationView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;

class RawNomenclatureToNomenclatureViewConverterTest extends TestCase
{
    public function testConvert()
    {
        $expectedResult = new NomenclatureItemView(
            '21300',
            '999',
            [
                'fr' => new NomenclatureItemTranslationView('Rouleaux vibrants', 'fr'),
                'en' => new NomenclatureItemTranslationView('Vibrating rollers', 'en'),
            ]
        );

        $nomenclatureLibTradFr = new \stdClass();
        $nomenclatureLibTradFr->referenceLangue = 'FRA';
        $nomenclatureLibTradFr->traduction = 'Rouleaux vibrants';

        $nomenclatureLibTradEn = new \stdClass();
        $nomenclatureLibTradEn->referenceLangue = 'GBR';
        $nomenclatureLibTradEn->traduction = 'Vibrating rollers';

        $rawNomenclature = new \stdClass();
        $rawNomenclature->reference = '21300';
        $rawNomenclature->code = '21300';
        $rawNomenclature->supprime = false;
        $rawNomenclature->referenceNomenclatureManifestationParent = '999';
        $rawNomenclature->nomenclatureLibTrad = [$nomenclatureLibTradFr, $nomenclatureLibTradEn];

        $rawNomenclatureToNomenclatureViewConverter = new RawNomenclatureToNomenclatureViewConverter();
        $result = $rawNomenclatureToNomenclatureViewConverter->convert($rawNomenclature);

        $this->assertEquals($expectedResult, $result);
    }
}
