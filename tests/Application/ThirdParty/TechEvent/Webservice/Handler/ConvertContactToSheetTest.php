<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\TechEvent\Webservice\Handler;

use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Configuration\Condition\TypeConverter;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Handler\ConvertContactToSheet;
use Proximum\Vimeet\Domain\Model\User;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\TechEvent\Webservice\Normalizer\ContactNormalizer;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as ExtraDataType;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Psr\Log\LoggerInterface;

class ConvertContactToSheetTest extends TestCase
{
    public function testHandle(): void
    {
        $user = $this->prophesize(User::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getUser()->willReturn($user->reveal());
        $event = $this->prophesize(Event::class);
        $event->getLocaleFallback()->willReturn('fr');
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $type1->getId()->willReturn(42);
        $type2->getId()->willReturn(1337);
        $registrationTemplate = $this->prophesize(TemplateData::class);
        $sheetTemplate = $this->prophesize(TemplateData::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $typeConverter = $this->prophesize(TypeConverter::class);

        $userEventExtraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $convertToParticipantHandler = $this->prophesize(ConvertToParticipantHandler::class);
        $dateTime = new \DateTime();
        $contactNormalizer = $this->prophesize(ContactNormalizer::class);
        $logger = $this->prophesize(LoggerInterface::class);

        $contact = [
            "IDCONTACT" => "113893672",
            "IDCONTACTMD5" => "0892e7d27bba322f0ac7fefbece15387",
            "SOCIETE" => "TPM",
            "IDCIVILITE" => "M  ",
            "NOM" => "Test",
            "PRENOM" => "MOHAMED",
            "TEL" => "0666778877",
            "GRADE" => "CF",
            "ADRESSE1" => "27 rue du test",
            "CODEPOSTAL" => "16006",
            "VILLE" => "ALGER",
            "IDPAYS" => "DZ",
            "EMAIL" => "example-1@example.net",
            "Secteur_activité" => "A99  ",
            "Préciser_Activite_Autre" => "TRANSPORT ET LOGISTIQUE",
            "Typologie_société" => "TP99 ",
            "Préciser_Typologie" => "PME",
            "Nombre_Personnes" => "T1   ",
            "Votre_Fonction" => "F02  ",
            "Nature_de_votre_société_organisation" => "D07  ",
            "Centres_intérêt" => "I01;I02;I03;I04;I05;I07",
            "Conference" => "Y",
            "RDV_B2B" => "0",
            "PASSWORD" => "AZERTYUIOPqsdfghjklm"
        ];

        $configuration = [
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

        $dataIndexedByTag = [
            "email" => "example-1@example.net",
            "sheet_title" => "TPM",
            "tag_sheet_generic_1" => "CF",
            "participant_gender" => "man",
            "participant_lastname" => "Test",
            "participant_firstname" => "MOHAMED",
            "sheet_address" => "27 rue du test",
            "sheet_zipcode" => "16006",
            "sheet_city" => "ALGER",
            "sheet_country" => "DZ",
            "sheet_phone" => "0666778877",
            "sheet_organization_category" => "TP99",
            "sheet_staff" => "T1",
            "participant_position" => "F02",
            "tag_sheet_generic_3" => "D07",
            "tag_sheet_generic_2" => false,
        ];

        $resultNormalizer = [
            "IDCONTACT" => "113893672",
            "IDCONTACTMD5" => "0892e7d27bba322f0ac7fefbece15387",
            "SOCIETE" => "TPM",
            "GRADE" => "CF",
            "IDCIVILITE" => "man",
            "NOM" => "Test",
            "PRENOM" => "MOHAMED",
            "TEL" => "0666778877",
            "ADRESSE1" => "27 rue du test",
            "CODEPOSTAL" => "16006",
            "VILLE" => "ALGER",
            "IDPAYS" => "DZ",
            "EMAIL" => "example-1@example.net",
            "Secteur_activité" => "A99",
            "Préciser_Activite_Autre" => "TRANSPORT ET LOGISTIQUE",
            "Typologie_société" => "TP99",
            "Préciser_Typologie" => "PME",
            "Nombre_Personnes" => "T1",
            "Votre_Fonction" => "F02",
            "Nature_de_votre_société_organisation" => "D07",
            "Centres_intérêt" => "I01;I02;I03;I04;I05;I07",
            "Conference" => "Y",
            "PASSWORD" => 'AZERTYUIOPqsdfghjklm',
            "RDV_B2B" => false
        ];

        $contactNormalizer->normalize($contact, $configuration['normalize'], 'IDPAYS')
            ->shouldBeCalled()
            ->willReturn($resultNormalizer)
        ;

        $typeConverter->convert([$type1->reveal(), $type2->reveal()], $configuration, $contact)
            ->shouldBeCalled()
            ->willReturn($type2->reveal())
        ;

        $convertToParticipantHandler->handle(
            new ConvertToParticipant(
                $event->reveal(),
                $type2->reveal(),
                $contact['EMAIL'],
                'fr',
                $dataIndexedByTag,
                $registrationTemplate->reveal(),
                $sheetTemplate->reveal(),
                ExtraDataType::IMPORTED_FROM_TECH_EVENT
            ))
            ->shouldBeCalled()
            ->willReturn($participant->reveal());

        $userEventExtraDataRepository->add(
            new User\Event\ExtraData(
                $user->reveal(),
                $event->reveal(),
                ExtraDataType::IMPORTED_FROM_TECH_EVENT,
                $contact['IDCONTACT'],
                $dateTime
            )
        )->shouldBeCalled();

        $userEventExtraDataRepository->removeForUserAndEventAndName(
            $user->reveal(),
            $event->reveal(),
            ExtraDataType::TECH_EVENT_LOGIN_DATA
        )->shouldBeCalled();

        $userEventExtraDataRepository->add(
            new User\Event\ExtraData(
                $user->reveal(),
                $event->reveal(),
                ExtraDataType::TECH_EVENT_LOGIN_DATA,
                $contact['PASSWORD'],
                $dateTime
            )
        )->shouldBeCalled();
        $userEventExtraDataRepository->add(
            new User\Event\ExtraData(
                $user->reveal(),
                $event->reveal(),
                ExtraDataType::TECH_EVENT_IDENTIFIER_MD5,
                $contact['IDCONTACTMD5'],
                $dateTime
            )
        )->shouldBeCalled();

        $handler = new ConvertContactToSheet(
            $userEventExtraDataRepository->reveal(),
            $convertToParticipantHandler->reveal(),
            $dateTime,
            $contactNormalizer->reveal(),
            $userRepository->reveal(),
            $typeConverter->reveal(),
            $logger->reveal()
        );
        $handler->handle(
            $event->reveal(),
            [$type1->reveal(), $type2->reveal()],
            [42 => $registrationTemplate->reveal(), 1337 => $registrationTemplate->reveal()],
            [42 => $sheetTemplate->reveal(), 1337 => $sheetTemplate->reveal()],
            $contact,
            $configuration
        );
    }
}
