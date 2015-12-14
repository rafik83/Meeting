<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Participant\Choice;

use Proximum\Vimeet\Application\Components\Library\Choice\ChoiceBuilder;
use Proximum\Vimeet\Application\Components\Library\Choice\Exception\LanguageNotFoundException;

class ChoiceBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function provideChoices()
    {
        return [
            [
                [
                    'position48' => ['label' => ['fr' => 'Ouvrier spécialisé', 'en' => 'Specialized worker']],
                    'position46' => ['label' => ['fr' => 'Conducteur de machine', 'en' => 'Machine driver']],
                    'position47' => ['label' => ['fr' => 'Opérateur de conditionnement', 'en' => 'Packaging operator']],
                ],
                'fr',
                [
                    'Conducteur de machine'        => 'position46',
                    'Opérateur de conditionnement' => 'position47',
                    'Ouvrier spécialisé'           => 'position48',
                ],
            ],
            [
                [
                    'position47' => ['label' => ['fr' => 'Opérateur de conditionnement', 'en' => 'Packaging operator']],
                    'position46' => ['label' => ['fr' => 'Conducteur de machine', 'en' => 'Machine driver']],
                    'position48' => ['label' => ['fr' => 'Ouvrier spécialisé', 'en' => 'Specialized worker']],
                ],
                'en',
                [
                    'Machine driver'     => 'position46',
                    'Packaging operator' => 'position47',
                    'Specialized worker' => 'position48',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideChoices
     *
     * @param array  $choices
     * @param string $locale
     * @param array  $expected
     */
    public function testBuildChoices(array $choices, $locale, array $expected)
    {
        $this->assertEquals($expected, (new ChoiceBuilder())->buildChoices($choices, $locale));
    }

    public function testBuildChoicesWithLabelNotFoundException()
    {
        $this->setExpectedException(LanguageNotFoundException::class);

        $choices = [
            'position46' => ['label' => ['fr' => 'Conducteur de machine', 'en' => 'Machine driver']],
            'position47' => ['label' => ['fr' => 'Opérateur de conditionnement', 'en' => 'Packaging operator']],
            'position48' => ['label' => ['fr' => 'Ouvrier spécialisé', 'en' => 'Specialized worker']],
        ];

        $builder = new ChoiceBuilder();
        $builder->buildChoices($choices, 'it');
    }

    public static function provideGroupedChoices()
    {
        return [
            [
                [
                    'domainPositionAchat' => [
                        'label' => ['fr' => 'Achat', 'en' => 'Buy'],
                        'choices' => [
                            'position1' => ['label' => ['fr' => 'Assistant achats', 'en' => 'Buyer assistant']],
                            'position2' => ['label' => ['fr' => 'Acheteur', 'en' => 'Buyer']],
                            'position3' => ['label' => ['fr' => 'Directeur des achats', 'en' => 'Buyer director']],
                        ],
                    ],
                    'domainPositionInformatique' => [
                        'label' => ['fr' => 'Informatique', 'en' => 'Computer science'],
                        'choices' => [
                            'position25' => ['label' => ['fr' => 'Technicien support clients', 'en' => 'Customer support technician']],
                            'position26' => ['label' => ['fr' => 'Technicien informatique', 'en' => 'Computer technician']],
                            'position27' => ['label' => ['fr' => 'Administrateur de base de données', 'en' => 'Database administrator']],
                         ],
                    ],
                ],
                'fr',
                [
                    'Achat' => [
                        'Assistant achats'     => 'position1',
                        'Acheteur'             => 'position2',
                        'Directeur des achats' => 'position3',
                    ],
                    'Informatique' => [
                        'Technicien support clients'        => 'position25',
                        'Technicien informatique'           => 'position26',
                        'Administrateur de base de données' => 'position27',
                    ]
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideGroupedChoices
     *
     * @param array  $choices
     * @param string $locale
     * @param array  $expected
     */
    public function testBuildGroupedChoices(array $choices, $locale, array $expected)
    {
        $this->assertEquals($expected, (new ChoiceBuilder())->buildGroupedChoices($choices, $locale));
    }

    public function testBuildGroupedChoicesWithLabelNotFoundException()
    {
        $this->setExpectedException(LanguageNotFoundException::class);

        $choices = [
            'domainPositionAchat' => [
                'label' => ['fr' => 'Achat', 'en' => 'Buy'],
                'choices' => [
                    'position1' => ['label' => ['fr' => 'Assistant achats', 'en' => 'Buyer assistant']],
                    'position2' => ['label' => ['fr' => 'Acheteur', 'en' => 'Buyer']],
                    'position3' => ['label' => ['fr' => 'Directeur des achats', 'en' => 'Buyer director']],
                ],
            ],
            'domainPositionInformatique' => [
                'label' => ['fr' => 'Informatique', 'en' => 'Computer science'],
                'choices' => [
                    'position25' => ['label' => ['fr' => 'Technicien support clients', 'en' => 'Customer support technician']],
                    'position26' => ['label' => ['fr' => 'Technicien informatique', 'en' => 'Computer technician']],
                    'position27' => ['label' => ['fr' => 'Administrateur de base de données', 'en' => 'Database administrator']],
                ],
            ],
        ];

        $builder = new ChoiceBuilder();
        $builder->buildGroupedChoices($choices, 'it');
    }

    public function provideChoiceAndGroupedChoices()
    {
        return [
            [
                [
                    'domainPositionAchat' => [
                        'label' => ['fr' => 'Achat', 'en' => 'Buy'],
                        'choices' => [
                            'position1' => ['label' => ['fr' => 'Assistant achats', 'en' => 'Buyer assistant']],
                            'position2' => ['label' => ['fr' => 'Acheteur', 'en' => 'Buyer']],
                            'position3' => ['label' => ['fr' => 'Directeur des achats', 'en' => 'Buyer director']],
                        ],
                    ],
                    'domainPositionInformatique' => [
                        'label' => ['fr' => 'Informatique', 'en' => 'Computer science'],
                        'choices' => [
                            'position25' => ['label' => ['fr' => 'Technicien support clients', 'en' => 'Customer support technician']],
                            'position26' => ['label' => ['fr' => 'Technicien informatique', 'en' => 'Computer technician']],
                            'position27' => ['label' => ['fr' => 'Administrateur de base de données', 'en' => 'Database administrator']],
                        ],
                    ],
                ],
                true,
            ],
            [
                [
                    'position48' => ['label' => ['fr' => 'Ouvrier spécialisé', 'en' => 'Specialized worker']],
                    'position46' => ['label' => ['fr' => 'Conducteur de machine', 'en' => 'Machine driver']],
                    'position47' => ['label' => ['fr' => 'Opérateur de conditionnement', 'en' => 'Packaging operator']],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideChoiceAndGroupedChoices
     *
     * @param $choices
     * @param $expected
     */
    public function testAreGroupedChoices($choices, $expected)
    {
        $this->assertEquals($expected, (new ChoiceBuilder())->areGroupedChoices($choices));
    }
}
