<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\TechEvent\Webservice\Handler;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Client\WSClient;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Configuration\MappingConfigurationChecker;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler\ConvertContactToSheet;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler\ImportSheetsHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Psr\Log\LoggerInterface;

class ImportSheetsHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $WSClient,
        $typeRepository,
        $templateDataFactory,
        $convertContactToSheet,
        $logger
    ;

    /** @var MappingConfigurationChecker */
    private $mappingConfigurationChecker;

    public function setUp(): void
    {
        $this->WSClient = $this->prophesize(WSClient::class);
        $this->typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $this->mappingConfigurationChecker = new MappingConfigurationChecker();
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->convertContactToSheet = $this->prophesize(ConvertContactToSheet::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    public function testImportSheetsHandler(): void
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(123);
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type1->getId()->shouldBeCalled()->willReturn(42);
        $type2->getId()->shouldBeCalled()->willReturn(1337);

        $configuration = [
            'endpoint' => 'https://example.net/test',
            'mandatory_keys' => [
                'email' => 'EMAIL',
                'identifier' => 'IDCONTACT',
                'identifierMD5' => 'IDCONTACTMD5',
                'country' => 'IDPAYS',
                'loginData' => 'PASSWORD',
            ],
            'types' => [
                '42' => [
                    'condition' => 'IDPAYS === EN',
                    'mapping' => [
                        "EMAIL" => "email",
                        "SOCIETE" => "sheet_title",
                        "GRADE" => "tag_sheet_generic_1",
                        "IDCIVILITE" => "participant_gender",
                        "NOM" => "participant_lastname",
                        "PRENOM" => "participant_firstname",
                        "ADRESSE1" => "sheet_address",
                        "ADRESSE2" => "sheet_address",
                        "CODEPOSTAL" => "sheet_zipcode",
                        "VILLE" => "sheet_city",
                        "IDPAYS" => "sheet_country",
                        "TEL" => "sheet_phone",
                        "Typologie_société" => "sheet_organization_category",
                        "Nombre_Personnes" => "sheet_staff",
                        "Votre_Fonction" => "participant_position",
                        "Nature_de_votre_société_organisation" => "tag_sheet_generic_3",
                        "RDV_B2B" => "tag_sheet_generic_2"
                    ],
                ],
                '1337' => [
                    'condition' => 'IDPAYS === FR',
                    'mapping' => [
                        "EMAIL" => "email",
                        "SOCIETE" => "sheet_title",
                        "GRADE" => "tag_sheet_generic_1",
                        "IDCIVILITE" => "participant_gender",
                        "NOM" => "participant_lastname",
                        "PRENOM" => "participant_firstname",
                        "ADRESSE1" => "sheet_address",
                        "ADRESSE2" => "sheet_address",
                        "CODEPOSTAL" => "sheet_zipcode",
                        "VILLE" => "sheet_city",
                        "IDPAYS" => "sheet_country",
                        "TEL" => "sheet_phone",
                        "Typologie_société" => "sheet_organization_category",
                        "Nombre_Personnes" => "sheet_staff",
                        "Votre_Fonction" => "participant_position",
                        "Nature_de_votre_société_organisation" => "tag_sheet_generic_3",
                        "RDV_B2B" => "tag_sheet_generic_2"
                    ],
                ],
            ],
            'normalize' => [
                "TEL" => "telephone",
                "IDCIVILITE" => "gender",
                "RDV_B2B" => "boolean"
            ]
        ];

        $this->typeRepository->getTypesByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([$type1->reveal(), $type2->reveal()])
        ;

        $template = $this->prophesize(TemplateData::class);
        $this->templateDataFactory
            ->createRegistrationFromType($type1->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($template->reveal())
        ;
        $this->templateDataFactory
            ->createRegistrationFromType($type2->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($template->reveal())
        ;

        $this->templateDataFactory
            ->createSheetTemplateFromType($type1->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($template->reveal())
        ;
        $this->templateDataFactory
            ->createSheetTemplateFromType($type2->reveal(), null)
            ->shouldBeCalled()
            ->willReturn($template->reveal())
        ;

        $this->logger->notice('VIMEET : Importing 2 contacts from techevent on event "123".')->shouldBeCalled();

        $contacts = [
            '12345' => [
                'IDCONTACT' => '12345',
                'email' => 'contact-1@example.net',
            ],
            '54321' => [
                'IDCONTACT' => '54321',
                'email' => 'contact-2@example.net',
            ],
        ];

        $this->convertContactToSheet
            ->handle(
                $event->reveal(),
                [$type1->reveal(), $type2->reveal()],
                [42 => $template->reveal(), 1337 => $template->reveal()],
                [42 => $template->reveal(), 1337 => $template->reveal()],
                [
                    'IDCONTACT' => '12345',
                    'email' => 'contact-1@example.net',
                ],
                $configuration
            )
            ->shouldBeCalled()
        ;

        $this->convertContactToSheet
            ->handle(
                $event->reveal(),
                [$type1->reveal(), $type2->reveal()],
                [42 => $template->reveal(), 1337 => $template->reveal()],
                [42 => $template->reveal(), 1337 => $template->reveal()],
                [
                    'IDCONTACT' => '54321',
                    'email' => 'contact-2@example.net',
                ],
                $configuration
            )
            ->shouldBeCalled()
        ;

        $this->WSClient->getContactsToSynchro('https://example.net/test', 'IDCONTACT')
            ->shouldBeCalled()
            ->willReturn($contacts)
        ;

        $importSheetsHandler = new ImportSheetsHandler(
            $this->WSClient->reveal(),
            $this->typeRepository->reveal(),
            $this->mappingConfigurationChecker,
            $this->templateDataFactory->reveal(),
            $this->convertContactToSheet->reveal(),
            $this->logger->reveal()
        );

        $importSheetsHandler->handle($event->reveal(), $configuration);
    }
}
