<?php

namespace Proximum\Vimeet\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class NomenclatureLevel1Test extends TestCase
{
    /** @var Nomenclature */
    private $nomenclature;

    public function setUp() {
        $this->nomenclature = new Nomenclature('Compétences', 1, [
            'ad987cae' => [
                'label' => ['fr' => 'Compétences Aéronautiques', 'en' => 'Aeronautical skills'],
                ],
        ]);
    }

    public function testGetLabel()
    {
        $nomenclature = $this->nomenclature;

        // Existing label
        $this->assertEquals('Compétences Aéronautiques', $nomenclature->getLabel('ad987cae', 'fr'));

        // Not existing label
        $this->assertEquals(null, $nomenclature->getLabel('b35ae0c7', 'fr'));

        // Not existing locale
        $this->assertEquals(null, $nomenclature->getLabel('b35ae0c7', 'de'));

        // Test fallback
        $this->assertEquals('Aeronautical skills', $nomenclature->getLabel('ad987cae', 'es', 'en'));
    }

    public function testGetLabels()
    {
        $nomenclature = $this->nomenclature;

        $expectedLabels = [
            'ad987cae' => 'Compétences Aéronautiques',
        ];

        $this->assertEquals($expectedLabels, $nomenclature->getLabels('fr'));
    }

    public function testGetTreeKeys()
    {
        $nomenclature = $this->nomenclature;

        $expectedLabels = [
            'ad987cae' => 'ad987cae',
        ];

        $this->assertEquals($expectedLabels, $nomenclature->getTreeKeys());
    }
}
