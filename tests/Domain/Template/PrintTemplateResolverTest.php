<?php

namespace Proximum\Vimeet\Tests\Domain\Template;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Template\AbstractChild;
use Proximum\Vimeet\Domain\Template\PrintTemplateResolver;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\ItemCollection;
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\Participant;
use Proximum\Vimeet\Domain\Template\TemplateObject\Tag;
use Proximum\Vimeet\Domain\Template\TemplateObject\Text;
use Proximum\Vimeet\Domain\View\Template\ResolvedPrintTemplateView;

class PrintTemplateResolverTest extends TestCase
{
    public function testResolve()
    {
        $locale = 'fr';
        $event = $this->prophesize(Event::class);
        $sheetTemplate = $this->prophesize(SheetTemplate::class);
        $sheetTemplate->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheetTemplate->getPrintValue()->willReturn(
            [
                'Mc601Mc73f' => [
                    'component' => 'block',
                    'type'      => '8-4',
                    'children'  => [
                        [
                            'M9d98Mc018' => [
                                'component' => 'block',
                                'type'      => '6-6',
                                'children'  => [
                                    [
                                        'd927a04b'   => ['component' => 'object'],
                                        'Med1eM86eb' => ['component' => 'object'],
                                        'Ma903M43a3' => ['component' => 'object'],
                                    ],
                                    [
                                        'Mf3e2Ma2f7' => ['component' => 'object'],
                                        'M69e5Med87' => [
                                            'component' => 'block',
                                            'type'      => '6-6',
                                            'children'  => [
                                                ['211efd1f' => ['component' => 'object']],
                                                ['a23a2f5d' => ['component' => 'object']],
                                            ],
                                            'config'    => ['style' => 'style1'],
                                        ],
                                    ],
                                ],
                                'config'    => ['style' => 'style2'],
                            ],
                        ],
                        [
                            'M58cfMcae8' => ['component' => 'object'],
                            'Macd4Md3ab' => ['component' => 'object'],
                            'Mca61M421a' => ['component' => 'object'],
                            'M0c23M9c66' => ['component' => 'object'],
                        ],
                    ],
                    'config'    => ['style' => 'style3'],
                ],
            ]
        );

        $expectedObjects = [
            'M58cfMcae8' => new Tag(
                'M58cfMcae8',
                AbstractChild::TEMPLATE_OBJECT_TYPE_TAG,
                [
                    'label' => [
                        'en' => 'Website',
                        'fr' => 'Site internet',
                    ],
                ],
                $locale,
                $locale
            ),
            'Macd4Md3ab' => new Tag(
                'Macd4Md3ab',
                AbstractChild::TEMPLATE_OBJECT_TYPE_TAG,
                [
                    'label' => [
                        'en' => 'Location',
                        'fr' => 'Localisation',
                    ],
                ],
                $locale,
                $locale
            ),
            'M8fb9M2792' => new Text(
                'M8fb9M2792',
                AbstractChild::TEMPLATE_OBJECT_TYPE_TEXT,
                [],
                $locale,
                $locale
            ),
            'Med1eM86eb' => new Nomenclature(
                'Med1eM86eb',
                AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
                [
                    'label' => [
                        'en' => 'Skills',
                        'fr' => 'Compétences',
                    ],
                ],
                $locale,
                $locale
            ),
            '1adc1873'   => new ItemCollection(
                '1adc1873',
                AbstractChild::TEMPLATE_OBJECT_TYPE_COLLECTION,
                [
                    'label' => [
                        'en' => 'References',
                        'fr' => 'Clients de référence',
                    ],
                ],
                $locale,
                $locale
            ),
            'Mf3e2Ma2f7' => new ItemCollection(
                'Mf3e2Ma2f7',
                AbstractChild::TEMPLATE_OBJECT_TYPE_COLLECTION,
                [
                    'label' => [
                        'en' => 'Innovative projects',
                        'fr' => 'Projets innovants',
                    ],
                ],
                $locale,
                $locale
            ),
            'MdaceM0e5c' => new ItemCollection(
                'MdaceM0e5c',
                AbstractChild::TEMPLATE_OBJECT_TYPE_COLLECTION,
                [
                    'label' => [
                        'en' => 'Equipments, technologies and processes',
                        'fr' => 'Equipements, technologies et process utilisés',
                    ],
                ],
                $locale,
                $locale
            ),
            'Ma903M43a3' => new Nomenclature(
                'Ma903M43a3',
                AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
                [
                    'label' => [
                        'en' => 'Application fields',
                        'fr' => "Domaines d'application",
                    ],
                ],
                $locale,
                $locale
            ),
            'd6fa1ac7'   => new Nomenclature(
                'd6fa1ac7',
                AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
                [
                    'label' => [
                        'en' => 'Landmarks',
                        'fr' => 'Eléments clés',
                    ],
                ],
                $locale,
                $locale
            ),
            'a9271999'   => new ItemCollection(
                'a9271999',
                AbstractChild::TEMPLATE_OBJECT_TYPE_COLLECTION,
                [
                    'label' => [
                        'en' => 'Group / Member of',
                        'fr' => 'Groupe / Membre',
                    ],
                ],
                $locale,
                $locale
            ),
            'a23a2f5d'   => new ItemCollection(
                'a23a2f5d',
                AbstractChild::TEMPLATE_OBJECT_TYPE_COLLECTION,
                [
                    'label' => [
                        'en' => 'Certifications',
                        'fr' => 'Certifications',
                    ],
                ],
                $locale,
                $locale
            ),
            'bef61d39'   => new Participant(
                'bef61d39',
                AbstractChild::TEMPLATE_OBJECT_TYPE_PARTICIPANT,
                [
                    'label'                    => [
                        'en' => 'Participants',
                        'fr' => 'Participants',
                    ],
                    'numberOfParticipantShown' => 3,
                ],
                $locale,
                $locale
            ),
            '211efd1f'   => new MediaCollection(
                '211efd1f',
                AbstractChild::TEMPLATE_OBJECT_TYPE_MEDIA,
                [
                    'label' => [
                        'en' => 'Medias',
                        'fr' => 'Médias',
                    ],
                ],
                $locale,
                $locale
            ),
            '27db4a35'   => new Text(
                '27db4a35',
                AbstractChild::TEMPLATE_OBJECT_TYPE_TEXT,
                [
                    'content' => [
                        'en' => 'Objectives',
                        'fr' => 'Objectifs de participation',
                    ],
                ],
                $locale,
                $locale
            ),
            'd927a04b'   => new EditableText(
                'd927a04b',
                AbstractChild::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT,
                [
                    'label' => [
                        'en' => 'Description',
                        'fr' => 'Description',
                    ],
                ],
                $locale,
                $locale
            ),
            'Mca61M421a' => new Nomenclature(
                'Mca61M421a',
                AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
                [
                    'label' => [
                        'en' => 'Offers',
                        'fr' => 'Offres',
                    ],
                ],
                $locale,
                $locale
            ),
            'M0c23M9c66' => new Nomenclature(
                'M0c23M9c66',
                AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
                [
                    'label' => [
                        'en' => 'Needs',
                        'fr' => 'Besoins',
                    ],
                ],
                $locale,
                $locale
            ),
        ];

        $templateData = $this->prophesize(TemplateData::class);
        $templateData
            ->getObjects()
            ->shouldBeCalled()
            ->willReturn($expectedObjects)
        ;

        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $templateDataFactory
            ->createFromTemplate($sheetTemplate->reveal())
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $expectedPrintTemplateObjects = [
           'M58cfMcae8' => new Tag(
               'M58cfMcae8',
               AbstractChild::TEMPLATE_OBJECT_TYPE_TAG,
               [
                   'label' => [
                       'en' => 'Website',
                       'fr' => 'Site internet',
                   ],
               ],
               $locale,
               $locale
           ),
           'Macd4Md3ab' => new Tag(
               'Macd4Md3ab',
               AbstractChild::TEMPLATE_OBJECT_TYPE_TAG,
               [
                   'label' => [
                       'en' => 'Location',
                       'fr' => 'Localisation',
                   ],
               ],
               $locale,
               $locale
           ),
           'Med1eM86eb' => new Nomenclature(
               'Med1eM86eb',
               AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
               [
                   'label' => [
                       'en' => 'Skills',
                       'fr' => 'Compétences',
                   ],
               ],
               $locale,
               $locale
           ),
           'Mf3e2Ma2f7' => new ItemCollection(
               'Mf3e2Ma2f7',
               AbstractChild::TEMPLATE_OBJECT_TYPE_COLLECTION,
               [
                   'label' => [
                       'en' => 'Innovative projects',
                       'fr' => 'Projets innovants',
                   ],
               ],
               $locale,
               $locale
           ),
           'Ma903M43a3' => new Nomenclature(
               'Ma903M43a3',
               AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
               [
                   'label' => [
                       'en' => 'Application fields',
                       'fr' => "Domaines d'application",
                   ],
               ],
               $locale,
               $locale
           ),
           'a23a2f5d'   => new ItemCollection(
               'a23a2f5d',
               AbstractChild::TEMPLATE_OBJECT_TYPE_COLLECTION,
               [
                   'label' => [
                       'en' => 'Certifications',
                       'fr' => 'Certifications',
                   ],
               ],
               $locale,
               $locale
           ),
           '211efd1f'   => new MediaCollection(
               '211efd1f',
               AbstractChild::TEMPLATE_OBJECT_TYPE_MEDIA,
               [
                   'label' => [
                       'en' => 'Medias',
                       'fr' => 'Médias',
                   ],
               ],
               $locale,
               $locale
           ),
           'd927a04b'   => new EditableText(
               'd927a04b',
               AbstractChild::TEMPLATE_OBJECT_TYPE_EDITABLE_TEXT,
               [
                   'label' => [
                       'en' => 'Description',
                       'fr' => 'Description',
                   ],
               ],
               $locale,
               $locale
           ),
           'Mca61M421a' => new Nomenclature(
               'Mca61M421a',
               AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
               [
                   'label' => [
                       'en' => 'Offers',
                       'fr' => 'Offres',
                   ],
               ],
               $locale,
               $locale
           ),
           'M0c23M9c66' => new Nomenclature(
               'M0c23M9c66',
               AbstractChild::TEMPLATE_OBJECT_TYPE_NOMENCLATURE,
               [
                   'label' => [
                       'en' => 'Needs',
                       'fr' => 'Besoins',
                   ],
               ],
               $locale,
               $locale
           ),
       ];

        $printTemplateData = $this->prophesize(TemplateData::class);
        $printTemplateData
            ->getObjects()
            ->shouldBeCalled()
            ->willReturn($expectedPrintTemplateObjects)
        ;

        $templateDataFactory
            ->create(Argument::type('array'), [], null, null, $event->reveal())
            ->shouldBeCalled()
            ->willReturn($printTemplateData->reveal())
        ;

        $printTemplateResolver = new PrintTemplateResolver($templateDataFactory->reveal());
        $result                = $printTemplateResolver->resolve($sheetTemplate->reveal());

        $expectedMissingObjects = [
            'M8fb9M2792' => [
                'component' => 'object',
                'type'      => 'text',
                'config'    => [],
            ],
            '1adc1873'   => [
                'component' => 'object',
                'type'      => 'collection',
                'config'    => [
                    'label' => [
                        'en' => 'References',
                        'fr' => 'Clients de référence',
                    ],
                ],
            ],
            'MdaceM0e5c' => [
                'component' => 'object',
                'type'      => 'collection',
                'config'    => [
                    'label' => [
                        'en' => 'Equipments, technologies and processes',
                        'fr' => 'Equipements, technologies et process utilisés',
                    ],
                ],
            ],
            'd6fa1ac7'   => [
                'component' => 'object',
                'type'      => 'nomenclature',
                'config'    => [
                    'label' => [
                        'en' => 'Landmarks',
                        'fr' => 'Eléments clés',
                    ],
                ],
            ],
            'a9271999'   => [
                'component' => 'object',
                'type'      => 'collection',
                'config'    => [
                    'label' => [
                        'en' => 'Group / Member of',
                        'fr' => 'Groupe / Membre',
                    ],
                ],
            ],
            'bef61d39'   => [
                'component' => 'object',
                'type'      => 'participant',
                'config'    => [
                    'label'                    => [
                        'en' => 'Participants',
                        'fr' => 'Participants',
                    ],
                    'numberOfParticipantShown' => 3,
                ],
            ],
            '27db4a35'   => [
                'component' => 'object',
                'type'      => 'text',
                'config'    => [
                    'content' => [
                        'en' => 'Objectives',
                        'fr' => 'Objectifs de participation',
                    ],
                ],
            ],
        ];

        $expectedPrintTemplateResolved = [
            'Mc601Mc73f' => [
                'component' => 'block',
                'type'      => '8-4',
                'children'  => [
                    [
                        'M9d98Mc018' => [
                            'component' => 'block',
                            'type'      => '6-6',
                            'children'  => [
                                [
                                    'd927a04b'   => [
                                        'component' => 'object',
                                        'type'      => 'editable-text',
                                        'config'    => [
                                            'label' => [
                                                'en' => 'Description',
                                                'fr' => 'Description',
                                            ],
                                        ],
                                    ],
                                    'Med1eM86eb' => [
                                        'component' => 'object',
                                        'type'      => 'nomenclature',
                                        'config'    => [
                                            'label' => [
                                                'en' => 'Skills',
                                                'fr' => 'Compétences',
                                            ],
                                        ],
                                    ],
                                    'Ma903M43a3' => [
                                        'component' => 'object',
                                        'type'      => 'nomenclature',
                                        'config'    => [
                                            'label' => [
                                                'en' => 'Application fields',
                                                'fr' => 'Domaines d\'application',
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'Mf3e2Ma2f7' => [
                                        'component' => 'object',
                                        'type'      => 'collection',
                                        'config'    => [
                                            'label' => [
                                                'en' => 'Innovative projects',
                                                'fr' => 'Projets innovants',
                                            ],
                                        ],
                                    ],
                                    'M69e5Med87' => [
                                        'component' => 'block',
                                        'type'      => '6-6',
                                        'children'  => [
                                            [
                                                '211efd1f' => [
                                                    'component' => 'object',
                                                    'type'      => 'medias',
                                                    'config'    => [
                                                        'label' => [
                                                            'en' => 'Medias',
                                                            'fr' => 'Médias',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                            [
                                                'a23a2f5d' => [
                                                    'component' => 'object',
                                                    'type'      => 'collection',
                                                    'config'    => [
                                                        'label' => [
                                                            'en' => 'Certifications',
                                                            'fr' => 'Certifications',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'config'    => ['style' => 'style1'],
                                    ],
                                ],
                            ],
                            'config'    => ['style' => 'style2'],
                        ],
                    ],
                    [
                        'M58cfMcae8' => [
                            'component' => 'object',
                            'type'      => 'tag',
                            'config'    => [
                                'label' => [
                                    'en' => 'Website',
                                    'fr' => 'Site internet',
                                ],
                            ],
                        ],
                        'Macd4Md3ab' => [
                            'component' => 'object',
                            'type'      => 'tag',
                            'config'    => [
                                'label' => [
                                    'en' => 'Location',
                                    'fr' => 'Localisation',
                                ],
                            ],
                        ],
                        'Mca61M421a' => [
                            'component' => 'object',
                            'type'      => 'nomenclature',
                            'config'    => [
                                'label' => [
                                    'en' => 'Offers',
                                    'fr' => 'Offres',
                                ],
                            ],
                        ],
                        'M0c23M9c66' => [
                            'component' => 'object',
                            'type'      => 'nomenclature',
                            'config'    => [
                                'label' => [
                                    'en' => 'Needs',
                                    'fr' => 'Besoins',
                                ],
                            ],
                        ],
                    ],
                ],
                'config'    => ['style' => 'style3'],
            ],
        ];

        $expectedResult = new ResolvedPrintTemplateView($expectedPrintTemplateResolved, $expectedMissingObjects);

        $this->assertEquals($expectedResult, $result);
    }
}
