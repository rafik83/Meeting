<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Handler;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Handler\SheetAndParticipantTemplateDataHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\Nomenclature\NomenclatureItemView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantPositionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\ParticipantView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationDescriptionView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\RegistrationView;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\View\SheetAndParticipantTemplateDataView;
use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\Country;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\Text;

class SheetAndParticipantTemplateDataHandlerTest extends TestCase
{
    public function testHandle()
    {
        $aloneNomenclatureItemView = new NomenclatureItemView('999', null, []);
        $parentNomenclatureItemView = new NomenclatureItemView('88898', null, []);
        $childNomenclatureItemView1 = new NomenclatureItemView('666', '88898', []);
        $childNomenclatureItemView1->setParentNomenclatureItemView($parentNomenclatureItemView);
        $childNomenclatureItemView2 = new NomenclatureItemView('1337', '88898', []);
        $childNomenclatureItemView2->setParentNomenclatureItemView($parentNomenclatureItemView);

        $nomenclatureItemViews = [
            $aloneNomenclatureItemView,
            $childNomenclatureItemView1,
            $childNomenclatureItemView2,
        ];

        $registrationView = new RegistrationView(
            '5556666',
            'Nintendo',
            'VALIDE',
            '61 rue de l\'Odyssée',
            '75008',
            'Paris',
            'FR',
            '33 (0)1 40 69 80 00',
            'https://www.nintendo.com',
            new ParticipantView(
                'man',
                'Takashi',
                'Kitano',
                'takashi.kitano@nintendo.com',
                'fr',
                null,
                'Nintendo Europe',
                [
                    new ParticipantPositionView('Directeur Export', 'fr'),
                    new ParticipantPositionView('Export Director', 'en'),
                ]
            ),
            $nomenclatureItemViews,
            [
                new RegistrationDescriptionView('Ma description en français', 'fr'),
                new RegistrationDescriptionView('My english description', 'en'),
            ]
        );

        /*
         * Registration Template
         */
        $notEditableObject = new Text('not-editable-key', 'gender', [], 'fr', 'fr');
        $genderObject = new Gender(
            'gender-key', 'gender', ['tags' => ['participant_gender', 'participant_data']], 'fr', 'fr'
        );
        $firstNameObject = new EditableText(
            'firstname-key', 'editable-text', ['tags' => ['participant_firstname', 'participant_data']], 'fr', 'fr'
        );
        $companyNameObject = new EditableText(
            'company-key', 'editable-text', ['tags' => ['sheet_organization', 'sheet_title', 'sheet_data']], 'fr', 'fr'
        );
        $companyCountryObject = new Country(
            'sheet-country-key', 'country', ['tags' => ['sheet_country', 'sheet_data']], 'fr', 'fr'
        );
        $countryObject = new Country(
            'participant-country-key', 'country', ['tags' => ['participant_country', 'participant_data']], 'fr', 'fr'
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

        $registrationTemplateBlock = new Block('12', [], 'fr', 'fr');
        $registrationTemplateBlock->addChild(1, '111', $firstNameObject);
        $registrationTemplateBlock->addChild(1, '222', $companyNameObject);
        $registrationTemplateBlock->addChild(1, '333', $notEditableObject);
        $registrationTemplateBlock->addChild(1, '444', $countryObject);
        $registrationTemplateBlock->addChild(1, '555', $companyCountryObject);
        $registrationTemplateBlock->addChild(1, '666', $genderObject);
        $registrationTemplateBlock->addChild(1, '777', $descriptionObject);
        $registrationTemplateBlock->addChild(1, '888', $positionObject);

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
            ->willReturn([
                'cmxp_111' => new NomenclatureItem('cmxp_111', []),
                'cmxp_999' => new NomenclatureItem('cmxp_999', []),
                'cmxp_7777' => new NomenclatureItem('cmxp_7777', []),
                'cmxp_88898' => new NomenclatureItem('cmxp_88898', []),
            ])
        ;

        $nomenclatureObject = new Nomenclature(
            'nomenclature-key',
            'nomenclature',
            [
                'tags' => ['sheet_template_generic_tag_1'],
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
            $registrationView,
            $registrationTemplateData,
            $sheetTemplateData
        );

        $expectedResult = new SheetAndParticipantTemplateDataView(
            'Nintendo',
            [
                'company-key' => ['text' => 'Nintendo'],
                'sheet-country-key' => ['country' => 'FR'],
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
                'position-key' => [
                    'text' => [
                        'fr' => 'Directeur Export',
                        'en' => 'Export Director',
                    ],
                ],
            ]
        );
        $expectedResult->setSheetTemplateData([
            'nomenclature-key' => ['items' => ['cmxp_999', 'cmxp_88898']],
        ]);

        $this->assertEquals($expectedResult, $result);
    }
}
