<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Participant\SheetAndParticipantTemplateDataHandler;
use Proximum\Vimeet\Application\View\Participant\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Country;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\Telephone;
use Proximum\Vimeet\Domain\Template\TemplateObject\Text;

class SheetAndParticipantTemplateDataHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dataIndexedByTag = [
            'sheet_title' => 'Nintendo',
            'sheet_country' => 'FR',
            'sheet_description' => [
                'en' => 'My english description',
                'fr' => 'Ma description en français',
            ],
            'sheet_organization_staff' => 'staff_nomenclature_item_2',
            'participant_gender' => 'man',
            'participant_firstname' => 'Takashi',
            'participant_lastname' => 'Kitano',
            'participant_country' => 'FR',
            'participant_mobile' => '33 (0)6 40 69 80 00',
            'participant_position' => [
                'fr' => 'Directeur Export',
                'en' => 'Export Director',
            ],
            'sheet_template_generic_tag_1' => [
                'unknown_item',
                'nomenclature_item_999',
                'nomenclature_item_999',
                'nomenclature_item_88898',
            ],
        ];

        $expectedResult = new SheetAndParticipantTemplateDataView(
            'Nintendo',
            [
                'company-key' => ['text' => 'Nintendo'],
                'sheet-country-key' => ['country' => 'FR'],
                'staff-nomenclature-key' => ['items' => ['staff_nomenclature_item_2']],
                'description-key' => [
                    'text' => [
                        'fr' => 'Ma description en français',
                        'en' => 'My english description',
                    ],
                ],
            ],
            [
                'gender-key' => ['gender' => 'man'],
                'firstname-key' => ['text' => 'Takashi'],
                'participant-country-key' => ['country' => 'FR'],
                'participant-phone-key' => ['telephone' => '33 (0)6 40 69 80 00'],
                'position-key' => [
                    'text' => [
                        'fr' => 'Directeur Export',
                        'en' => 'Export Director',
                    ],
                ],
            ],
            [
                'nomenclature-key' => [
                    'items' => [
                        'nomenclature_item_999',
                        'nomenclature_item_88898',
                    ],
                ],
            ]
        );

        /*
         * Registration Template
         */
        $notEditableObject = new Text('not-editable-key', 'gender', [], 'fr', 'fr');
        $genderObject = new Gender(
            'gender-key',
            'gender',
            [
                'tags' => [
                    'participant_gender',
                    'participant_data',
                ],
            ],
            'fr',
            'fr'
        );
        $firstNameObject = new EditableText(
            'firstname-key',
            'editable-text',
            [
                'tags' => [
                    'participant_firstname',
                    'participant_data',
                ],
            ],
            'fr',
            'fr'
        );
        $companyNameObject = new EditableText(
            'company-key',
            'editable-text',
            [
                'tags' => [
                    'sheet_organization',
                    'sheet_title',
                    'sheet_data',
                ],
            ],
            'fr',
            'fr'
        );
        $companyCountryObject = new Country(
            'sheet-country-key',
            'country',
            [
                'tags' => [
                    'sheet_country',
                    'sheet_data',
                ],
            ],
            'fr',
            'fr'
        );
        $countryObject = new Country(
            'participant-country-key',
            'country',
            [
                'tags' => [
                    'participant_country',
                    'participant_data',
                ],
            ],
            'fr',
            'fr'
        );
        $mobileObject = new Telephone(
            'participant-phone-key',
            'country',
            [
                'tags' => [
                    'participant_mobile',
                    'participant_data',
                ],
            ],
            'fr',
            'fr'
        );
        $descriptionObject = new EditableText(
            'description-key',
            'editable-text',
            [
                'translatable' => true,
                'tags' => [
                    'sheet_description',
                    'sheet_data',
                ],
            ],
            'fr',
            'fr'
        );
        $positionObject = new EditableText(
            'position-key',
            'editable-text',
            [
                'translatable' => true,
                'tags' => [
                    'participant_position',
                    'participant_data',
                ],
            ],
            'fr',
            'fr'
        );

        $staffNomenclatureModel = $this->prophesize(NomenclatureModel::class);
        $staffNomenclatureModel->getId()->shouldBeCalled()->willReturn(1969);
        $staffNomenclatureModel
            ->getLastLevel()
            ->shouldBeCalled()
            ->willReturn(
                [
                    'staff_nomenclature_item_1' => new NomenclatureItem('staff_nomenclature_item_1', []),
                    'staff_nomenclature_item_2' => new NomenclatureItem('staff_nomenclature_item_2', []),
                ]
            )
        ;

        $staffNomenclatureObject = new Nomenclature(
            'staff-nomenclature-key',
            'nomenclature',
            [
                'tags' => [
                    'sheet_organization_staff',
                    'sheet_data',
                ],
                'mode' => 'singles',
            ],
            'fr',
            'fr'
        );
        $staffNomenclatureObject->setNomenclature($staffNomenclatureModel->reveal());

        $registrationTemplateBlock = new Block('12', [], 'fr', 'fr');
        $registrationTemplateBlock->addChild(1, '111', $firstNameObject);
        $registrationTemplateBlock->addChild(1, '222', $companyNameObject);
        $registrationTemplateBlock->addChild(1, '333', $notEditableObject);
        $registrationTemplateBlock->addChild(1, '444', $countryObject);
        $registrationTemplateBlock->addChild(1, '555', $companyCountryObject);
        $registrationTemplateBlock->addChild(1, '666', $genderObject);
        $registrationTemplateBlock->addChild(1, '777', $descriptionObject);
        $registrationTemplateBlock->addChild(1, '888', $positionObject);
        $registrationTemplateBlock->addChild(1, '999', $mobileObject);
        $registrationTemplateBlock->addChild(1, '1000', $staffNomenclatureObject);

        $registrationTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $registrationTemplateData->addChild(0, '67019e4a', $registrationTemplateBlock);

        /*
         * Sheet Template
         */
        $nomenclatureModel = $this->prophesize(NomenclatureModel::class);
        $nomenclatureModel->getId()->shouldBeCalled()->willReturn(1969);
        $nomenclatureModel
            ->getLastLevel()
            ->shouldBeCalled()
            ->willReturn(
                [
                    'nomenclature_item_999' => new NomenclatureItem('nomenclature_item_999', []),
                    'nomenclature_item_1111' => new NomenclatureItem('nomenclature_item_1111', []),
                    'nomenclature_item_7777' => new NomenclatureItem('nomenclature_item_7777', []),
                    'nomenclature_item_88898' => new NomenclatureItem('nomenclature_item_88898', []),
                ]
            )
        ;

        $nomenclatureObject = new Nomenclature(
            'nomenclature-key',
            'nomenclature',
            [
                'tags' => [
                    'sheet_template_generic_tag_1',
                    'sheet_data',
                ],
                'mode' => 'checkboxes',
            ],
            'fr',
            'fr'
        );
        $nomenclatureObject->setNomenclature($nomenclatureModel->reveal());

        $sheetTemplateBlock = new Block('12', [], 'fr', 'fr');
        $sheetTemplateBlock->addChild(1, '111', $nomenclatureObject);
        $sheetTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $sheetTemplateData->addChild(0, '67019e4a', $sheetTemplateBlock);

        /*
         * Handler
         */
        $sheetAndParticipantTemplateDataHandler = new SheetAndParticipantTemplateDataHandler();
        $result = $sheetAndParticipantTemplateDataHandler->handle(
            $dataIndexedByTag,
            $registrationTemplateData,
            $sheetTemplateData
        );

        $this->assertEquals($expectedResult, $result);
    }
}
