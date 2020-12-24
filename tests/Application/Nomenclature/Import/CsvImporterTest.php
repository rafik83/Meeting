<?php

namespace Proximum\Vimeet\Tests\Application\Nomenclature;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Proximum\Vimeet\Application\Nomenclature\Import\CsvImporter;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\InvalidLocaleException;
use Proximum\Vimeet\Application\Nomenclature\Import\Exception\LocalesMustCorrespondToThoseOfTheEventException;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Tests\Application\Nomenclature\Id\StaticIdGenerator;

class CsvImporterTest extends TestCase
{
    public function testGenerator()
    {
        $ids = [
            'aaaaaa', 'aaaaab', 'aaaaac', 'aaaaad', 'aaaaae', 'aaaaaf', 'aaaaag', 'aaaaah', 'aaaaai', 'aaaaaj', 'aaaaak', 'aaaaal', 'aaaaam', 'aaaaan', 'aaaaao', 'aaaaap', 'aaaaaq', 'aaaaar', 'aaaaas', 'aaaaat', 'aaaaau', 'aaaaav', 'aaaaaw', 'aaaaax', 'aaaaay', 'aaaaaz',
            'aaaaba', 'aaaabb', 'aaaabc', 'aaaabd', 'aaaabe', 'aaaabf', 'aaaabg', 'aaaabh', 'aaaabi', 'aaaabj', 'aaaabk', 'aaaabl', 'aaaabm', 'aaaabn', 'aaaabo', 'aaaabp', 'aaaabq', 'aaaabr', 'aaaabs', 'aaaabt', 'aaaabu', 'aaaabv', 'aaaabw', 'aaaabx', 'aaaaby', 'aaaabz',
        ];

        $generator = new StaticIdGenerator($ids);

        foreach ($ids as $id) {
            $this->assertEquals($id, $generator->generate());
        }

        // Assert a second time to check the loop works
        foreach ($ids as $id) {
            $this->assertEquals($id, $generator->generate());
        }
    }

    public function testImport1Level()
    {
        $ids = [
            'aaaaaa', 'aaaaab', 'aaaaac', 'aaaaad', 'aaaaae', 'aaaaaf', 'aaaaag', 'aaaaah', 'aaaaai', 'aaaaaj', 'aaaaak', 'aaaaal', 'aaaaam', 'aaaaan', 'aaaaao', 'aaaaap', 'aaaaaq', 'aaaaar', 'aaaaas', 'aaaaat', 'aaaaau', 'aaaaav', 'aaaaaw', 'aaaaax', 'aaaaay', 'aaaaaz',
            'aaaaba', 'aaaabb', 'aaaabc', 'aaaabd', 'aaaabe', 'aaaabf', 'aaaabg', 'aaaabh', 'aaaabi', 'aaaabj', 'aaaabk', 'aaaabl', 'aaaabm', 'aaaabn', 'aaaabo', 'aaaabp', 'aaaabq', 'aaaabr', 'aaaabs', 'aaaabt', 'aaaabu', 'aaaabv', 'aaaabw', 'aaaabx', 'aaaaby', 'aaaabz',
        ];

        $intl = $this->prophesize(IntlInterface::class);
        $intl->getLocales()->willReturn(['es', 'fr', 'en']);

        $generator    = new StaticIdGenerator($ids);
        $importer     = new CsvImporter($generator, $intl->reveal());
        $nomenclature = new Nomenclature('Offres et besoins');
        $expected     = new Nomenclature('Offres et besoins', 1, [
            'aaaaaa' => [
                'label' => [
                    'fr' => 'Brevets',
                    'en' => 'Brevets en',
                ],
            ],
            'aaaaab' => [
                'label' => [
                    'fr' => 'conception',
                    'en' => 'conception en',
                ],
            ],
            'aaaaac' => ['label' => [
                    'fr' => 'conseil et/ou services',
                    'en' => 'conseil et/ou services en',
                ],
            ],
            'aaaaad' => ['label' => [
                    'fr' => 'Design',
                    'en' => 'Design en',
                ],
            ],
            'aaaaae' => ['label' => [
                    'fr' => 'Etude et ingénierie',
                    'en' => 'Etude et ingénierie en',
                ],
            ],
            'aaaaaf' => [
                'label' => [
                    'fr' => 'Mise à disposition de ressources humaines spécifiques ponctuelles',
                    'en' => 'Mise à disposition de ressources humaines spécifiques ponctuelles en',
                ],
            ],
            'aaaaag' => [
                'label' => [
                    'fr' => 'Partenariat',
                    'en' => 'Partenariat en',
                ],
            ],
            'aaaaah' => [
                'label' => [
                    'fr' => 'Prototypage',
                    'en' => 'Prototypage en',
                ],
            ],
            'aaaaai' => [
                'label' => [
                    'fr' => 'R&D contractuelle',
                    'en' => 'R&D contractuelle en',
                ],
            ],
            'aaaaaj' => [
                'label' => [
                    'fr' => 'Recherche partenariale',
                    'en' => 'Recherche partenariale en',
                ],
            ],
            'aaaaak' => [
                'label' => [
                    'fr' => 'Simulation, modélisation, calcul',
                    'en' => 'Simulation, modélisation, calcul en',
                ],
            ],
            'aaaaal' => [
                'label' => [
                    'fr' => 'Transfert de technologies',
                    'en' => 'Transfert de technologies en',
                ],
            ],
        ]);

        $importer->import($nomenclature, __DIR__ . '/offres_besoins.csv', Charset::UTF_8);

        $this->assertEquals($expected, $nomenclature);
    }

    public function testImport3Levels()
    {
        $ids = [
            'aaaaaa', 'aaaaab', 'aaaaac', 'aaaaad', 'aaaaae', 'aaaaaf', 'aaaaag', 'aaaaah', 'aaaaai', 'aaaaaj', 'aaaaak', 'aaaaal', 'aaaaam', 'aaaaan', 'aaaaao', 'aaaaap', 'aaaaaq', 'aaaaar', 'aaaaas', 'aaaaat', 'aaaaau', 'aaaaav', 'aaaaaw', 'aaaaax', 'aaaaay', 'aaaaaz',
            'aaaaba', 'aaaabb', 'aaaabc', 'aaaabd', 'aaaabe', 'aaaabf', 'aaaabg', 'aaaabh', 'aaaabi', 'aaaabj', 'aaaabk', 'aaaabl', 'aaaabm', 'aaaabn', 'aaaabo', 'aaaabp', 'aaaabq', 'aaaabr', 'aaaabs', 'aaaabt', 'aaaabu', 'aaaabv', 'aaaabw', 'aaaabx', 'aaaaby', 'aaaabz',
        ];

        $intl = $this->prophesize(IntlInterface::class);
        $intl->getLocales()->willReturn(['es', 'fr', 'en']);

        $generator    = new StaticIdGenerator($ids);
        $importer     = new CsvImporter($generator, $intl->reveal());
        $nomenclature = new Nomenclature('Offres et besoins');
        $expected     = new Nomenclature('Offres et besoins', 3, [
            'aaaaaa' => [
                'label' => [
                    'fr' => 'compétences AERO FR',
                    'en' => 'Aero en',
                ],
                'children' => [
                    'aaaaab' => [
                        'label' => [
                            'fr' => 'ELECTRONIQUE, ELECTROMAGNETIQUE & ELECTRICITE (FR)',
                            'en' => 'ELECTRONIC, ELECTROMAGNETICS & ELECTRICITY (EN)',
                        ],
                        'children' => [
                            'aaaaac' => [
                                'label' => [
                                    'fr' => 'Antennes et réseaux',
                                    'en' => 'Antennas and networks',
                                ],
                            ],
                            'aaaaad' => [
                                'label' => [
                                    'fr' => 'Bobinage',
                                    'en' => 'Winding',
                                ],
                            ],
                            'aaaaae' => [
                                'label' => [
                                    'fr' => 'Câblage',
                                    'en' => 'Wiring',
                                ],
                            ],
                            'aaaaaf' => [
                                'label' => [
                                    'fr' => 'Capteurs, contrôles, mesures',
                                    'en' => 'Sensors, controls, measures',
                                ],
                            ],
                        ],
                    ],
                    'aaaaag' => [
                        'label' => [
                            'fr' => 'ENERGIE & PUISSANCE (FR)',
                            'en' => 'ENERGY & POWER (EN)',
                        ],
                        'children' => [
                            'aaaaah' => [
                                'label' => [
                                    'fr' => 'Batteries / Accumulateurs d\'énergie',
                                    'en' => 'Batteries / Energy accumulators',
                                ],
                            ],
                            'aaaaai' => [
                                'label' => [
                                    'fr' => 'Autres types de stockage d\'énergie (piles a combustibles, roues d\'inertie)',
                                    'en' => 'Other types of energy storage (fuel element, inertia weel)',
                                ],
                            ],
                            'aaaaaj' => [
                                'label' => [
                                    'fr' => 'Cellules solaires et panneaux solaires',
                                    'en' => 'Solar cells et panels',
                                ],
                            ],
                        ],
                    ],
                    'aaaaak' => [
                        'label' => [
                            'fr' => 'OPTIQUE ET OPTRONIQUE (FR)',
                            'en' => 'OPTICS AND OPTRONICS (EN)',
                        ],
                        'children' => [
                            'aaaaal' => [
                                'label' => [
                                    'fr' => 'Analyse et ingénierie systèmes',
                                    'en' => 'Analysis and system engineering',
                                ],
                            ],
                            'aaaaam' => [
                                'label' => [
                                    'fr' => 'Composants optiques et capteurs',
                                    'en' => 'Optic and sensor components',
                                ],
                            ],
                            'aaaaan' => [
                                'label' => [
                                    'fr' => 'Dispositifs optroniques',
                                    'en' => 'Optronic dispositives',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'aaaaao' => [
                'label' => [
                    'fr' => 'compétences Spactial fr',
                    'en' => 'spacial en',
                ],
                'children' => [
                    'aaaaap' => [
                        'label' => [
                            'fr' => 'DETECTION & TRAITEMENT DE SIGNAUX (FR)',
                            'en' => 'SIGNAL DETECTION & PROCESSING (EN)',
                        ],
                        'children' => [
                            'aaaaaq' => [
                                'label' => [
                                    'fr' => 'Compression, cryptage',
                                    'en' => 'Compression, encryption',
                                ],
                            ],
                            'aaaaar' => [
                                'label' => [
                                    'fr' => 'Contrôle',
                                    'en' => 'Control',
                                ],
                            ],
                            'aaaaas' => [
                                'label' => [
                                    'fr' => 'Guidage',
                                    'en' => 'Guidance',
                                ],
                            ],
                        ],
                    ],
                    'aaaaat' => [
                        'label' => [
                            'fr' => 'MESURES, CONTROLES ESSAIS TESTS (FR)',
                            'en' => 'MEASUREMENTS AND CONTROL TESTS (EN)',
                        ],
                        'children' => [
                            'aaaaau' => [
                                'label' => [
                                    'fr' => 'Bancs et conduite d\'essais',
                                    'en' => '',
                                ],
                            ],
                            'aaaaav' => [
                                'label' => [
                                    'fr' => 'Contrôle non destructif',
                                    'en' => '',
                                ],
                            ],
                            'aaaaaw' => [
                                'label' => [
                                    'fr' => 'Equipements de mesure',
                                    'en' => '',
                                ],
                            ],
                        ],
                    ],
                    'aaaaax' => [
                        'label' => [
                            'fr' => 'INGENIERIE, R&D (FR)',
                            'en' => 'ENGINEERING, R&D (EN)',
                        ],
                        'children' => [
                            'aaaaay' => [
                                'label' => [
                                    'fr' => 'Accompagnement technique industriel (CTI)',
                                    'en' => 'Industrial technical support',
                                ],
                            ],
                            'aaaaaz' => [
                                'label' => [
                                    'fr' => 'Bureau d\'études',
                                    'en' => 'Design office',
                                ],
                            ],
                            'aaaaba' => [
                                'label' => [
                                    'fr' => 'Co-développement',
                                    'en' => 'Co-development',
                                ],
                            ],
                            'aaaabb' => [
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

        $importer->import($nomenclature, __DIR__ . '/competences.csv', Charset::UTF_8);

        $this->assertEquals($expected, $nomenclature);
    }

    public function testImport3LevelsWindows1252()
    {
        $ids = [
            'aaaaaa', 'aaaaab', 'aaaaac', 'aaaaad', 'aaaaae', 'aaaaaf', 'aaaaag', 'aaaaah', 'aaaaai', 'aaaaaj', 'aaaaak', 'aaaaal', 'aaaaam', 'aaaaan', 'aaaaao', 'aaaaap', 'aaaaaq', 'aaaaar', 'aaaaas', 'aaaaat', 'aaaaau', 'aaaaav', 'aaaaaw', 'aaaaax', 'aaaaay', 'aaaaaz',
            'aaaaba', 'aaaabb', 'aaaabc', 'aaaabd', 'aaaabe', 'aaaabf', 'aaaabg', 'aaaabh', 'aaaabi', 'aaaabj', 'aaaabk', 'aaaabl', 'aaaabm', 'aaaabn', 'aaaabo', 'aaaabp', 'aaaabq', 'aaaabr', 'aaaabs', 'aaaabt', 'aaaabu', 'aaaabv', 'aaaabw', 'aaaabx', 'aaaaby', 'aaaabz',
        ];

        $intl = $this->prophesize(IntlInterface::class);
        $intl->getLocales()->willReturn(['es', 'fr', 'en']);

        $generator    = new StaticIdGenerator($ids);
        $importer     = new CsvImporter($generator, $intl->reveal());
        $nomenclature = new Nomenclature('Offres et besoins');
        $expected     = new Nomenclature('Offres et besoins', 3, [
            'aaaaaa' => [
                'label' => [
                    'fr' => 'compétences AERO FR',
                    'en' => 'Aero en',
                ],
                'children' => [
                    'aaaaab' => [
                        'label' => [
                            'fr' => 'ELECTRONIQUE, ELECTROMAGNETIQUE & ELECTRICITE (FR)',
                            'en' => 'ELECTRONIC, ELECTROMAGNETICS & ELECTRICITY (EN)',
                        ],
                        'children' => [
                            'aaaaac' => [
                                'label' => [
                                    'fr' => 'Antennes et réseaux',
                                    'en' => 'Antennas and networks',
                                ],
                            ],
                            'aaaaad' => [
                                'label' => [
                                    'fr' => 'Bobinage',
                                    'en' => 'Winding',
                                ],
                            ],
                            'aaaaae' => [
                                'label' => [
                                    'fr' => 'Câblage',
                                    'en' => 'Wiring',
                                ],
                            ],
                            'aaaaaf' => [
                                'label' => [
                                    'fr' => 'Capteurs, contrôles, mesures',
                                    'en' => 'Sensors, controls, measures',
                                ],
                            ],
                        ],
                    ],
                    'aaaaag' => [
                        'label' => [
                            'fr' => 'ENERGIE & PUISSANCE (FR)',
                            'en' => 'ENERGY & POWER (EN)',
                        ],
                        'children' => [
                            'aaaaah' => [
                                'label' => [
                                    'fr' => 'Batteries / Accumulateurs d’énergie',
                                    'en' => 'Batteries / Energy accumulators',
                                ],
                            ],
                            'aaaaai' => [
                                'label' => [
                                    'fr' => 'utres types de stockage d’énergie (piles a combustibles, roues d’inertie …)',
                                    'en' => 'Other types of energy storage (fuel element, inertia weel …)',
                                ],
                            ],
                            'aaaaaj' => [
                                'label' => [
                                    'fr' => 'Cellules solaires et panneaux solaires',
                                    'en' => 'Solar cells et panels',
                                ],
                            ],
                        ],
                    ],
                    'aaaaak' => [
                        'label' => [
                            'fr' => 'OPTIQUE ET OPTRONIQUE (FR)',
                            'en' => 'OPTICS AND OPTRONICS (EN)',
                        ],
                        'children' => [
                            'aaaaal' => [
                                'label' => [
                                    'fr' => 'Analyse et ingénierie systèmes',
                                    'en' => 'Analysis and system engineering',
                                ],
                            ],
                            'aaaaam' => [
                                'label' => [
                                    'fr' => 'Composants optiques et capteurs',
                                    'en' => 'Optic and sensor components',
                                ],
                            ],
                            'aaaaan' => [
                                'label' => [
                                    'fr' => 'Dispositifs optroniques',
                                    'en' => 'Optronic dispositives',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'aaaaao' => [
                'label' => [
                    'fr' => 'compétences Spactial fr',
                    'en' => 'spacial en',
                ],
                'children' => [
                    'aaaaap' => [
                        'label' => [
                            'fr' => 'DETECTION & TRAITEMENT DE SIGNAUX (FR)',
                            'en' => 'SIGNAL DETECTION & PROCESSING (EN)',
                        ],
                        'children' => [
                            'aaaaaq' => [
                                'label' => [
                                    'fr' => 'Compression, cryptage',
                                    'en' => 'Compression, encryption',
                                ],
                            ],
                            'aaaaar' => [
                                'label' => [
                                    'fr' => 'Contrôle',
                                    'en' => 'Control',
                                ],
                            ],
                            'aaaaas' => [
                                'label' => [
                                    'fr' => 'Guidage',
                                    'en' => 'Guidance',
                                ],
                            ],
                        ],
                    ],
                    'aaaaat' => [
                        'label' => [
                            'fr' => 'MESURES, CONTROLES ESSAIS TESTS (FR)',
                            'en' => 'MEASUREMENTS AND CONTROL TESTS (EN)',
                        ],
                        'children' => [
                            'aaaaau' => [
                                'label' => [
                                    'fr' => 'Bancs et conduite d’essais',
                                    'en' => '',
                                ],
                            ],
                            'aaaaav' => [
                                'label' => [
                                    'fr' => 'Contrôle non destructif',
                                    'en' => '',
                                ],
                            ],
                            'aaaaaw' => [
                                'label' => [
                                    'fr' => 'Equipements de mesure',
                                    'en' => '',
                                ],
                            ],
                        ],
                    ],
                    'aaaaax' => [
                        'label' => [
                            'fr' => 'INGENIERIE, R&D (FR)',
                            'en' => 'ENGINEERING, R&D (EN)',
                        ],
                        'children' => [
                            'aaaaay' => [
                                'label' => [
                                    'fr' => 'Accompagnement technique industriel (CTI)',
                                    'en' => 'Industrial technical support',
                                ],
                            ],
                            'aaaaaz' => [
                                'label' => [
                                    'fr' => 'Bureau d’études',
                                    'en' => 'Design office',
                                ],
                            ],
                            'aaaaba' => [
                                'label' => [
                                    'fr' => 'Co-développement',
                                    'en' => 'Co-development',
                                ],
                            ],
                            'aaaabb' => [
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

        $importer->import($nomenclature, __DIR__ . '/competences_windows1252.csv', Charset::WINDOWS_1252);

        $this->assertEquals($expected, $nomenclature);
    }

    public function testUpdate3Levels()
    {
        $ids = [
            'aaaaaa', 'aaaaab', 'aaaaac', 'aaaaad', 'aaaaae', 'aaaaaf', 'aaaaag', 'aaaaah', 'aaaaai', 'aaaaaj', 'aaaaak', 'aaaaal', 'aaaaam', 'aaaaan', 'aaaaao', 'aaaaap', 'aaaaaq', 'aaaaar', 'aaaaas', 'aaaaat', 'aaaaau', 'aaaaav', 'aaaaaw', 'aaaaax', 'aaaaay', 'aaaaaz',
            'aaaaba', 'aaaabb', 'aaaabc', 'aaaabd', 'aaaabe', 'aaaabf', 'aaaabg', 'aaaabh', 'aaaabi', 'aaaabj', 'aaaabk', 'aaaabl', 'aaaabm', 'aaaabn', 'aaaabo', 'aaaabp', 'aaaabq', 'aaaabr', 'aaaabs', 'aaaabt', 'aaaabu', 'aaaabv', 'aaaabw', 'aaaabx', 'aaaaby', 'aaaabz',
        ];

        $intl = $this->prophesize(IntlInterface::class);
        $intl->getLocales()->willReturn(['es', 'fr', 'en']);

        $generator    = new StaticIdGenerator($ids);
        $importer     = new CsvImporter($generator, $intl->reveal());
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

        $expected = new Nomenclature('Offres et besoins', 3, [
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
                                    'fr' => 'Batteries et Accumulateurs d\'énergie',
                                    'en' => 'Batteries and Energy accumulators',
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
                            'aaaaaa' => [
                                'label' => [
                                    'fr' => 'foobar',
                                    'en' => 'barfoo',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $importer->import($nomenclature, __DIR__ . '/competences_update.csv', Charset::UTF_8);

        $this->assertEquals($expected, $nomenclature);
    }

    public function testInvalidLocaleException()
    {
        $intl = $this->prophesize(IntlInterface::class);
        $intl->getLocales()->willReturn(['es', 'fr', 'en']);

        $importer  = new CsvImporter($this->prophesize(StaticIdGenerator::class)->reveal(), $intl->reveal());

        $this->expectException(InvalidLocaleException::class);
        $importer->import(new Nomenclature('Nomenclature title'), __DIR__ . '/wrong-locale.csv', Charset::UTF_8);
    }

    public function testLocalesCorrespondingToThoseOfTheEvent()
    {
        $ids = [
            'aaaaaa', 'aaaaab', 'aaaaac', 'aaaaad', 'aaaaae', 'aaaaaf', 'aaaaag', 'aaaaah', 'aaaaai', 'aaaaaj', 'aaaaak', 'aaaaal', 'aaaaam', 'aaaaan', 'aaaaao', 'aaaaap', 'aaaaaq', 'aaaaar', 'aaaaas', 'aaaaat', 'aaaaau', 'aaaaav', 'aaaaaw', 'aaaaax', 'aaaaay', 'aaaaaz',
            'aaaaba', 'aaaabb', 'aaaabc', 'aaaabd', 'aaaabe', 'aaaabf', 'aaaabg', 'aaaabh', 'aaaabi', 'aaaabj', 'aaaabk', 'aaaabl', 'aaaabm', 'aaaabn', 'aaaabo', 'aaaabp', 'aaaabq', 'aaaabr', 'aaaabs', 'aaaabt', 'aaaabu', 'aaaabv', 'aaaabw', 'aaaabx', 'aaaaby', 'aaaabz',
        ];

        $generator = new StaticIdGenerator($ids);

        $event = $this->prophesize(Event::class);
        $event->getLocales()->willReturn(['fr', 'en']);

        $intl = $this->prophesize(IntlInterface::class);
        $intl->getLocales()->shouldBeCalled()->willReturn(['en', 'fr']);

        $importer = new CsvImporter($generator, $intl->reveal());
        $importer->import(new Nomenclature('Nomenclature title', 3), __DIR__ . '/competences.csv', Charset::UTF_8);
    }

    public function testLocalesMustCorrespondToThoseOfTheEventException()
    {
        $event = $this->prophesize(Event::class);
        $event->getLocales()->willReturn(['fr', 'es']);

        $intl = $this->prophesize(IntlInterface::class);
        $intl->getLocales()->willReturn(['es', 'fr', 'en']);

        $importer  = new CsvImporter($this->prophesize(StaticIdGenerator::class)->reveal(), $intl->reveal());

        $this->expectException(LocalesMustCorrespondToThoseOfTheEventException::class);

        // The Event is in "fr / es", and we import a "en / fr" file
        $importer->import(
            new Nomenclature('Nomenclature title', 1, [], true, $event->reveal()),
            __DIR__ . '/competences_update.csv',
            Charset::UTF_8
        );
    }
}
