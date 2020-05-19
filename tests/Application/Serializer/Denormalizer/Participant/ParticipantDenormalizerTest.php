<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Serializer\Denormalizer\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantDenormalizer;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantImportLogger;
use Proximum\Vimeet\Domain\Account\EmailValidator;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature as NomenclatureModel;
use Proximum\Vimeet\Domain\Model\NomenclatureItem;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Participant\ParticipantOfSheetWithPackageParticipantAndPlanningDisabled;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\Country;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;
use Proximum\Vimeet\Domain\Template\TemplateObject\Telephone;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\SerializerAdapter;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

class ParticipantDenormalizerTest extends TestCase
{
    public function testDenormalize()
    {
        $datetime = new \DateTime();
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $locale = 'fr';
        $filename = __DIR__ . '/import_participants.csv';

        $userAlreadyExists = $this->prophesize(User::class);
        $userAlreadyExists->getEmail()->shouldBeCalled()->willReturn('julie@gmail.com');

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $userEventRepository = $this->prophesize(UserEventRepositoryInterface::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $emailValidator = $this->prophesize(EmailValidator::class);
        $synchronizer = $this->prophesize(Synchronizer::class);
        $translatorAdapter = $this->prophesize(TranslatorInterface::class);
        $participantOfSheetWithPackageParticipantAndPlanningDisabled = $this->prophesize(
            ParticipantOfSheetWithPackageParticipantAndPlanningDisabled::class
        );

        $userRepository->findByEmail('julie@gmail.com')->shouldBeCalled()->willReturn($userAlreadyExists->reveal());
        $userRepository->findByEmail('jean@gmail.com')->shouldBeCalled()->willReturn(null);

        $errorMessages = [
            'validators.admin.sheet.import_participant.error.country',
            'validators.admin.sheet.participant_import.email.error',
            'validators.admin.sheet.participant_import.email.exist.error',
            'validators.admin.sheet.participant_import.gender.error',
            'validators.admin.sheet.participant_import.nomenclature.error',
            'validators.admin.sheet.participant_import.telephone.error',
        ];

        foreach ($errorMessages as $errorMessage) {
            $translatorAdapter->trans($errorMessage, [], 'validators', $locale)->willReturn($errorMessage);
        }

        $participantLogger = new ParticipantImportLogger($translatorAdapter->reveal());

        $sheetRepository
            ->getOwnerEmails($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                1 => [
                    'email' => 'martine@gmail.com'
                ]
            ])
        ;

        $templateData = new TemplateData('root', [], 'fr', 'fr');

        $block = new Block('12', [], 'fr', 'fr');

        $firstname = new EditableText(
            'firstname',
            'editable-text',
            ['tags' => ['participant_firstname', 'participant_data']],
            'fr',
            'fr'
        );

        $lastname = new EditableText(
            'lastname',
            'editable-text',
            ['tags' => ['participant_lastname', 'participant_data']],
            'fr',
            'fr'
        );

        $company = new EditableText(
            'company',
            'editable-text',
            ['tags' => ['sheet_organization', 'sheet_data']],
            'fr',
            'fr'
        );

        $mobile = new Telephone(
            'mobile',
            'telephone',
            ['tags' => ['participant_phone', 'participant_data']],
            'fr',
            'fr'
        );

        $country = new Country('country', 'country', ['tags' => ['sheet_country', 'sheet_data']], 'fr', 'fr');

        $block->addChild(1, 'firstname', $firstname);
        $block->addChild(1, 'lastname', $lastname);
        $block->addChild(1, 'company', $company);
        $block->addChild(1, 'mobile', $mobile);
        $block->addChild(1, 'country', $country);
        $templateData->addChild(0, 'block', $block);

        $sheetTemplateData = new TemplateData('root', [], 'fr', 'fr');
        $sheetBlock = new Block('12', [], 'fr', 'fr');
        $sheetTitle = new EditableText('sheetTitle', 'editable-text', ['tags' => []], 'fr', 'fr');
        $description = new EditableText('description', 'editable-text', ['tags' => []], 'fr', 'fr');
        $nomenclature = new Nomenclature('nomenclature', 'nomenclature', ['tags' => []], 'fr', 'fr');
        $nomenclatureModel = $this->prophesize(NomenclatureModel::class);
        $nomenclature->setNomenclature($nomenclatureModel->reveal());
        $item1 = new NomenclatureItem('nomenclatureKey1', ['fr' => 'item1'], [], false);
        $item2 = new NomenclatureItem('nomenclatureKey2', ['fr' => 'item2'], [], false);
        $nomenclatureModel->getLastLevel()->shouldBeCalled()->willReturn([$item1, $item2]);

        $sheetBlock->addChild(1, 'sheetTitle', $sheetTitle);
        $sheetBlock->addChild(1, 'description', $description);
        $sheetBlock->addChild(1, 'nomenclature', $nomenclature);
        $sheetTemplateData->addChild(0, 'block', $sheetBlock);

        $templateDataFactory
            ->createRegistrationFromType($type, $locale)
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;
        $templateDataFactory
            ->createSheetTemplateFromType($type, $locale)
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData)
        ;

        $mapping = [
            'Nom participant' => 'lastname',
            'Prénom participant' => 'firstname',
            'Société Acheteur' => 'company',
            'E-mail Acheteur' => 'participant_import.field.mail',
            'Mobile' => 'mobile',
            'Pays Acheteur' => 'country',
            'Titre de la fiche' => 'sheetTitle',
            'Description' => 'description',
            'Nomenclature' => 'nomenclature',
        ];

        $emailValidator->validate('')->willReturn(false);
        $emailValidator->validate('jean@gmail.com')->willReturn(true);
        $emailValidator->validate('nicolas@gmail.com')->willReturn(true);
        $emailValidator->validate('martine@gmail.com')->willReturn(true);
        $emailValidator->validate('julie@gmail.com')->willReturn(true);
        $emailValidator->validate('zinedine@example.net')->willReturn(true);

        // Add sheet for "User already exists in DB" for julie@gmail.com
        $sheet1 = new Sheet($event->reveal(), $type->reveal(), [], $userAlreadyExists->reveal(), $datetime);
        $sheet1->setImported(true);
        $sheet1->setTitle('User already exists in DB');

        $participant1 = new Participant($sheet1, $userAlreadyExists->reveal(), [], false, $datetime);
        $participant1->setImported(true);

        $sheet1->setRegistrationData(
            ['company' => ['text' => 'User already exists in DB'], 'country' => ['country' => 'FR']]
        );

        $sheetRepository
            ->add(
                Argument::that(
                    static function (Sheet $sheet) {
                        return 'User already exists in DB' === $sheet->getTitle()
                            && $sheet->isImported()
                        ;
                    }
                )
            )
            ->shouldBeCalled()
        ;

        $userEventRepository
            ->add(new UserEvent($userAlreadyExists->reveal(), $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $taggedData1 = [
            'participant_firstname' => 'Julie',
            'participant_lastname' => 'KL',
            'participant_phone' => '+33666667700',
            'sheet_organization' => 'User already exists in DB',
        ];
        $synchronizer->set($templateData->setTaggedData($taggedData1), $userAlreadyExists->reveal())->shouldBeCalled();

        // Add sheet for "Ma Petite Tribu" for jean@gmail.com
        $user2 = new User('jean@gmail.com', '', '', 'fr');
        $userRepository->add($user2)->shouldBeCalled();

        $sheet2 = new Sheet($event->reveal(), $type->reveal(), ['sheetTitle' => ['text' => 'Ma Petite Tribu']], $user2, $datetime);
        $sheet2->setImported(true);
        $sheet2->setTitle('Ma Petite Tribu');

        $participant2 = new Participant($sheet2, $user2, [], false, $datetime);
        $participant2->setImported(true);

        $sheet2->setRegistrationData(['company' => ['text' => 'Ma Petite Tribu'], 'country' => ['country' => 'FR']]);
        $sheetRepository
            ->add(
                Argument::that(
                    static function (Sheet $sheet) {
                        return 'Ma Petite Tribu' === $sheet->getTitle()
                            && $sheet->isImported()
                        ;
                    }
                )
            )
            ->shouldBeCalled()
        ;

        $callBackParticipant1 = Argument::that(
            static function (Participant $participant) {
                return 'User already exists in DB' === $participant->getSheet()->getTitle()
                    && 'julie@gmail.com' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );
        $participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant1)
            ->shouldBeCalled()
        ;

        $callBackParticipant2 = Argument::that(
            function (Participant $participant) {
                return 'Ma Petite Tribu' === $participant->getSheet()->getTitle()
                    && 'jean@gmail.com' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );

        $participantRepository
            ->add($callBackParticipant2)
            ->shouldBeCalled()
        ;
        $participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant2)
            ->shouldBeCalled()
        ;

        $userEventRepository
            ->add(new UserEvent($user2, $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $taggedData2 = [
            'participant_firstname' => 'Jean',
            'participant_lastname' => 'CD',
            'participant_phone' => '+33666667788',
            'sheet_organization' => 'Ma Petite Tribu',
        ];
        $synchronizer->set($templateData->setTaggedData($taggedData2), $user2)->shouldBeCalled();

        $participantRepository
            ->getParticipantEmailsForEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                1 => [
                    'email' => 'email-not-used@gmail.com',
                ]
            ])
        ;

        $participantRepository
            ->add(Argument::that(static function(Participant $participant) {
                return $participant->getEmail() === 'jean@gmail.com'
                    || $participant->getEmail() === 'julie@gmail.com'
                ;
            }))
            ->shouldBeCalled()
        ;

        // Configure Serializer
        $serializer = new Serializer(
            [
                new ParticipantDenormalizer(
                    $participantRepository->reveal(),
                    $userRepository->reveal(),
                    $sheetRepository->reveal(),
                    $userEventRepository->reveal(),
                    $templateDataFactory->reveal(),
                    $emailValidator->reveal(),
                    $synchronizer->reveal(),
                    $participantLogger,
                    $participantOfSheetWithPackageParticipantAndPlanningDisabled->reveal(),
                    $datetime
                ),
            ],
            [
                new CsvEncoder(),
            ]
        );

        $serializerAdapter = new SerializerAdapter($serializer);

        /** @var ParticipantImportLogger $importLogger */
        $importLogger = $serializerAdapter->deserialize(
            file_get_contents($filename),
            Participant::class,
            'csv',
            [
                'csv_delimiter' => ';',
                'mappings'      => $mapping,
                'event'         => $event->reveal(),
                'type'          => $type->reveal(),
                'locale'        => $locale,
            ]
        );

        $expected = [
            'existing_participations' => 1,
            'file_participations' => 7,
            'created_sheets' => 2,
            'created_users' => 1,
            'import_errors' => [
                '2;validators.admin.sheet.import_participant.error.country;France',
                '5;validators.admin.sheet.participant_import.email.error;',
                '6;validators.admin.sheet.participant_import.email.exist.error;jean@gmail.com',
                '8;validators.admin.sheet.participant_import.nomenclature.error;item5',
            ],
        ];

        $this->assertEquals($expected, $importLogger->toArray());
    }
}
