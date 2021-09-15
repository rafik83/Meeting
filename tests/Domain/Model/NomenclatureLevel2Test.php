<?php

namespace Proximum\Vimeet\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class NomenclatureLevel2Test extends TestCase
{
    /** @var Nomenclature */
    private $nomenclature;

    public function setUp() {
        $this->nomenclature = new Nomenclature('Compétences', 2, [
            'ad987cae' => [
                'label' => ['fr' => 'Compétences Aéronautiques', 'en' => 'Aeronautical skills'],
                'children' => [
                    'ecc44d0d' => [
                        'label' => ['fr' => 'Ingénierie & Bureau d\'études', 'en' => 'Engineering and Engineering consulting firm'],
                    ],
                    '32ef03cc' => [
                        'label' => ['fr' => 'Informatique', 'en' => 'Computing'],
                    ],
                    '2ec033da' => [
                        'label' => ['fr' => 'Instrumentation Appareils de mesures scientifiques in-situ', 'en' => 'Instrumentation in situ scientific Measuring devices'],
                    ],
                ],
            ],
        ]);
    }

    public function testGetLabel()
    {
        $nomenclature = $this->nomenclature;

        // Existing label
        $this->assertEquals('Compétences Aéronautiques', $nomenclature->getLabel('ad987cae', 'fr'));
        $this->assertEquals('Ingénierie & Bureau d\'études', $nomenclature->getLabel('ecc44d0d', 'fr'));

        // Not existing label
        $this->assertEquals(null, $nomenclature->getLabel('b35ae0c7', 'fr'));

        // Not existing locale
        $this->assertEquals(null, $nomenclature->getLabel('b35ae0c7', 'de'));

        // Test fallback
        $this->assertEquals('Computing', $nomenclature->getLabel('32ef03cc', 'es', 'en'));
    }

    public function testGetLabels()
    {
        $nomenclature = $this->nomenclature;

        $expectedLabels = [
            'Compétences Aéronautiques' => [
                'ecc44d0d' => 'Ingénierie & Bureau d\'études',
                '32ef03cc' => 'Informatique',
                '2ec033da' => 'Instrumentation Appareils de mesures scientifiques in-situ',
            ],
        ];

        $this->assertEquals($expectedLabels, $nomenclature->getLabels('fr'));
    }

    public function testGetTreeKeys()
    {
        $nomenclature = $this->nomenclature;

        $expectedLabels = [
            'ad987cae' => [
                'ecc44d0d' => 'ecc44d0d',
                '32ef03cc' => '32ef03cc',
                '2ec033da' => '2ec033da',
            ],
        ];

        $this->assertEquals($expectedLabels, $nomenclature->getTreeKeys());
    }
}
