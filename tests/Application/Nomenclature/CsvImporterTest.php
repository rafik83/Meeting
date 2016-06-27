<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Nomenclature;

use Proximum\Vimeet\Application\Nomenclature\CsvImporter;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Tests\Application\Nomenclature\Id\StaticIdGenerator;

class CsvImporterTest extends \PHPUnit_Framework_TestCase
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

        $generator    = new StaticIdGenerator($ids);
        $importer     = new CsvImporter($generator);
        $nomenclature = new Nomenclature('Offres et besoins', 1, [
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

        $this->assertEquals($nomenclature, $importer->import('Offres et besoins', __DIR__.'/offres_besoins.csv'));
    }

    public function testImport2Levels()
    {
        $ids = [
            'aaaaaa', 'aaaaab', 'aaaaac', 'aaaaad', 'aaaaae', 'aaaaaf', 'aaaaag', 'aaaaah', 'aaaaai', 'aaaaaj', 'aaaaak', 'aaaaal', 'aaaaam', 'aaaaan', 'aaaaao', 'aaaaap', 'aaaaaq', 'aaaaar', 'aaaaas', 'aaaaat', 'aaaaau', 'aaaaav', 'aaaaaw', 'aaaaax', 'aaaaay', 'aaaaaz',
            'aaaaba', 'aaaabb', 'aaaabc', 'aaaabd', 'aaaabe', 'aaaabf', 'aaaabg', 'aaaabh', 'aaaabi', 'aaaabj', 'aaaabk', 'aaaabl', 'aaaabm', 'aaaabn', 'aaaabo', 'aaaabp', 'aaaabq', 'aaaabr', 'aaaabs', 'aaaabt', 'aaaabu', 'aaaabv', 'aaaabw', 'aaaabx', 'aaaaby', 'aaaabz',
        ];

        $generator    = new StaticIdGenerator($ids);
        $importer     = new CsvImporter($generator);
        $nomenclature = new Nomenclature('Offres et besoins', 1, [
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
                                ]
                            ],
                            'aaaaaz' => [
                                'label' => [
                                    'fr' => 'Bureau d\'études',
                                    'en' => 'Design office',
                                ]
                            ],
                            'aaaaba' => [
                                'label' => [
                                    'fr' => 'Co-développement',
                                    'en' => 'Co-development',
                                ]
                            ],
                            'aaaabb' => [
                                'label' => [
                                    'fr' => 'Crédit impôt recherche',
                                    'en' => 'Research tax credit',
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals($nomenclature, $importer->import('Offres et besoins', __DIR__.'/competences.csv'));
    }
}
