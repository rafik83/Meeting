<?php

namespace Proximum\Vimeet\Tests\Application\Nomenclature\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Nomenclature\Export\CsvExporter;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class CsvExporterTest extends TestCase
{
    public function testExport()
    {
        $nomenclature = new Nomenclature('Offres et besoins', 3, [
            '5770ec3cf1356' => [
                'label' => [
                    'fr' => 'compétences AERO FR',
                    'en' => 'Aero en',
                ],
                'children' => [
                    '5770ec3d74734' => [
                        'label' => [
                            'fr' => 'ELECTRONIQUE, ELECTROMAGNETIQUE & ELECTRICITE (FR)',
                            'en' => 'ELECTRONIC, ELECTROMAGNETICS & ELECTRICITY (EN)',
                        ],
                        'children' => [
                            '5770ec3e07503' => [
                                'label' => [
                                    'fr' => 'Antennes et réseaux',
                                    'en' => 'Antennas and networks',
                                ],
                            ],
                            '5770ec3e8667e' => [
                                'label' => [
                                    'fr' => 'Bobinage',
                                    'en' => 'Winding',
                                ],
                            ],
                            '5770ec3f107b0' => [
                                'label' => [
                                    'fr' => 'Câblage',
                                    'en' => 'Wiring',
                                ],
                            ],
                            '5770ec3f83918' => [
                                'label' => [
                                    'fr' => 'Capteurs, contrôles, mesures',
                                    'en' => 'Sensors, controls, measures',
                                ],
                            ],
                        ],
                    ],
                    '5770ec400eddc' => [
                        'label' => [
                            'fr' => 'ENERGIE & PUISSANCE (FR)',
                            'en' => 'ENERGY & POWER (EN)',
                        ],
                        'children' => [
                            '5770ec4083e3e' => [
                                'label' => [
                                    'fr' => 'Batteries / Accumulateurs d\'énergie',
                                    'en' => 'Batteries / Energy accumulators',
                                ],
                            ],
                            '5770ec410bcbc' => [
                                'label' => [
                                    'fr' => 'Autres types de stockage d\'énergie (piles a combustibles, roues d\'inertie)',
                                    'en' => 'Other types of energy storage (fuel element, inertia weel)',
                                ],
                            ],
                            '5770ec418f8eb' => [
                                'label' => [
                                    'fr' => 'Cellules solaires et panneaux solaires',
                                    'en' => 'Solar cells et panels',
                                ],
                            ],
                        ],
                    ],
                    '5770ec4216adf' => [
                        'label' => [
                            'fr' => 'OPTIQUE ET OPTRONIQUE (FR)',
                            'en' => 'OPTICS AND OPTRONICS (EN)',
                        ],
                        'children' => [
                            '5770ec4299edf' => [
                                'label' => [
                                    'fr' => 'Analyse et ingénierie systèmes',
                                    'en' => 'Analysis and system engineering',
                                ],
                            ],
                            '5770ec431ca59' => [
                                'label' => [
                                    'fr' => 'Composants optiques et capteurs',
                                    'en' => 'Optic and sensor components',
                                ],
                            ],
                            '5770ec4397496' => [
                                'label' => [
                                    'fr' => 'Dispositifs optroniques',
                                    'en' => 'Optronic dispositives',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            '5770ec4418ec5' => [
                'label' => [
                    'fr' => 'compétences Spactial fr',
                    'en' => 'spacial en',
                ],
                'children' => [
                    '5770ec449068d' => [
                        'label' => [
                            'fr' => 'DETECTION & TRAITEMENT DE SIGNAUX (FR)',
                            'en' => 'SIGNAL DETECTION & PROCESSING (EN)',
                        ],
                        'children' => [
                            '5770ec4504638' => [
                                'label' => [
                                    'fr' => 'Compression, cryptage',
                                    'en' => 'Compression, encryption',
                                ],
                            ],
                            '5770ec456c2c3' => [
                                'label' => [
                                    'fr' => 'Contrôle',
                                    'en' => 'Control',
                                ],
                            ],
                            '5770ec45cfa99' => [
                                'label' => [
                                    'fr' => 'Guidage',
                                    'en' => 'Guidance',
                                ],
                            ],
                        ],
                    ],
                    '5770ec463a79b' => [
                        'label' => [
                            'fr' => 'MESURES, CONTROLES ESSAIS TESTS (FR)',
                            'en' => 'MEASUREMENTS AND CONTROL TESTS (EN)',
                        ],
                        'children' => [
                            '5770ec4697276' => [
                                'label' => [
                                    'fr' => 'Bancs et conduite d\'essais',
                                    'en' => '',
                                ],
                            ],
                            '5770ec46ecb18' => [
                                'label' => [
                                    'fr' => 'Contrôle non destructif',
                                    'en' => '',
                                ],
                            ],
                            '5770ec4743883' => [
                                'label' => [
                                    'fr' => 'Equipements de mesure',
                                    'en' => '',
                                ],
                            ],
                        ],
                    ],
                    '5770ec47914b5' => [
                        'label' => [
                            'fr' => 'INGENIERIE, R&D (FR)',
                            'en' => 'ENGINEERING, R&D (EN)',
                        ],
                        'children' => [
                            '5770ec47d8f49' => [
                                'label' => [
                                    'fr' => 'Accompagnement technique industriel (CTI)',
                                    'en' => 'Industrial technical support',
                                ],
                            ],
                            '5770ec482e8ff' => [
                                'label' => [
                                    'fr' => 'Bureau d\'études',
                                    'en' => 'Design office',
                                ],
                            ],
                            '5770ec4872d29' => [
                                'label' => [
                                    'fr' => 'Co-développement',
                                    'en' => 'Co-development',
                                ],
                            ],
                            '5770ec48bdee6' => [
                                'label' => [
                                    'fr' => 'Crédit impôt recherche',
                                    'en' => 'Research tax credit',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $exporter = new CsvExporter();
        $output   = '/tmp/nomenclature_' . uniqid();
        $result = $exporter->export($nomenclature, $output, Charset::UTF_8);

        $this->assertInstanceOf(\SplFileObject::class, $result);
    }
}
