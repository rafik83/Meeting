<?php

namespace Proximum\Vimeet\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class NomenclatureLevel3Test extends TestCase
{
    /** @var Nomenclature */
    private $nomenclature;

    public function setUp() {
        $this->nomenclature = new Nomenclature('Compétences', 3, [
            'ad987cae' => [
                'label' => ['fr' => 'Compétences Aéronautiques', 'en' => 'Aeronautical skills'],
                'children' => [
                    'ecc44d0d' => [
                        'label' => ['fr' => 'Ingénierie & Bureau d\'études', 'en' => 'Engineering and Engineering consulting firm'],
                        'children' => [
                            'c93def9a' => ['label' => ['fr' => 'Modélisation et calculs', 'en' => 'Modelling and calculations']],
                            '34eab90c' => ['label' => ['fr' => 'Expérimentation & réalisation de prototypes', 'en' => 'Experiment and realization of prototypes']],
                        ],
                    ],
                    '32ef03cc' => [
                        'label' => ['fr' => 'Informatique', 'en' => 'Computing'],
                        'children' => [
                            'cab0332d' => ['label' => ['en' => 'Modelling and simulation']],
                        ],
                    ],
                    '2ec033da' => [
                        'label' => ['fr' => 'Instrumentation Appareils de mesures scientifiques in-situ', 'en' => 'Instrumentation in situ scientific Measuring devices'],
                        'children' => [
                            'aaa34eb9' => ['label' => ['fr' => 'Appareils de mesures scientifiques in-situ', 'en' => 'In situ scientific measuring devices']],
                            'bdec99a0' => ['label' => ['fr' => 'Prototypage', 'en' => 'Prototypage']],
                            'b35ae9c7' => ['label' => ['fr' => 'Technologie laser', 'en' => 'Laser technology']],
                        ],
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
        $this->assertEquals('Experiment and realization of prototypes', $nomenclature->getLabel('34eab90c', 'en'));
        $this->assertEquals('Technologie laser', $nomenclature->getLabel('b35ae9c7', 'fr'));

        // Not existing label
        $this->assertEquals(null, $nomenclature->getLabel('b35ae0c7', 'fr'));

        // Not existing locale
        $this->assertEquals(null, $nomenclature->getLabel('b35ae0c7', 'de'));

        // Test fallback
        $this->assertEquals('Modelling and simulation', $nomenclature->getLabel('cab0332d', 'fr', 'en'));
    }

    public function testGetLabels()
    {
        $nomenclature = $this->nomenclature;

        $expectedLabels = [
            'Compétences Aéronautiques' => [
                'Ingénierie & Bureau d\'études'                              => [
                    'c93def9a' => 'Modélisation et calculs',
                    '34eab90c' => 'Expérimentation & réalisation de prototypes',
                ],
                'Informatique'                                               => [
                    'cab0332d' => '',
                ],
                'Instrumentation Appareils de mesures scientifiques in-situ' => [
                    'aaa34eb9' => 'Appareils de mesures scientifiques in-situ',
                    'bdec99a0' => 'Prototypage',
                    'b35ae9c7' => 'Technologie laser',
                ],
            ],
        ];

        $this->assertEquals($expectedLabels, $nomenclature->getLabels('fr'));
    }

    public function testGetTreeKeys()
    {
        $nomenclature = $this->nomenclature;

        $expectedLabels = [
            'ad987cae' => [
                'ecc44d0d' => [
                    'c93def9a' => 'c93def9a',
                    '34eab90c' => '34eab90c',
                ],
                '32ef03cc' => [
                    'cab0332d' => 'cab0332d',
                ],
                '2ec033da' => [
                    'aaa34eb9' => 'aaa34eb9',
                    'bdec99a0' => 'bdec99a0',
                    'b35ae9c7' => 'b35ae9c7',
                ],
            ],
        ];

        $this->assertEquals($expectedLabels, $nomenclature->getTreeKeys());
    }
}
