<?php

namespace Proximum\Vimeet\Tests\Application\Serializer\Denormalizer\Participant;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
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
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
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
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Serializer;

class ParticipantDenormalizerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $participantRepository,
        $userRepository,
        $sheetRepository,
        $userEventRepository,
        $groupRepository,
        $templateDataFactory,
        $emailValidator,
        $synchronizer,
        $translatorAdapter,
        $participantOfSheetWithPackageParticipantAndPlanningDisabled
    ;

    /** @var \DateTime */
    private $dateTime;

    public function setUp(): void
    {
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->userRepository = $this->prophesize(UserRepositoryInterface::class);
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->userEventRepository = $this->prophesize(UserEventRepositoryInterface::class);
        $this->groupRepository = $this->prophesize(GroupRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->emailValidator = $this->prophesize(EmailValidator::class);
        $this->synchronizer = $this->prophesize(Synchronizer::class);
        $this->translatorAdapter = $this->prophesize(TranslatorInterface::class);
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled = $this->prophesize(
            ParticipantOfSheetWithPackageParticipantAndPlanningDisabled::class
        );
        $this->dateTime = new \DateTime();
    }

    public function testDenormalize(): void
    {
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $locale = 'fr';
        $filename = __DIR__ . '/import_participants.csv';

        $userAlreadyExists = $this->prophesize(User::class);
        $userAlreadyExists->getEmail()->shouldBeCalled()->willReturn('julie@gmail.com');

        $this->userRepository->findByEmail('julie@gmail.com')->shouldBeCalled()->willReturn($userAlreadyExists->reveal());
        $this->userRepository->findByEmail('jean@gmail.com')->shouldBeCalled()->willReturn(null);

        $errorMessages = [
            'validators.admin.sheet.import_participant.error.country',
            'validators.admin.sheet.participant_import.email.error',
            'validators.admin.sheet.participant_import.email.exist.error',
            'validators.admin.sheet.participant_import.gender.error',
            'validators.admin.sheet.participant_import.nomenclature.error',
            'validators.admin.sheet.participant_import.telephone.error',
        ];

        foreach ($errorMessages as $errorMessage) {
            $this->translatorAdapter->trans($errorMessage, [], 'validators', $locale)->willReturn($errorMessage);
        }

        $participantLogger = new ParticipantImportLogger($this->translatorAdapter->reveal());

        $this->sheetRepository
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

        $this->templateDataFactory
            ->createRegistrationFromType($type, $locale)
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;
        $this->templateDataFactory
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

        $this->emailValidator->validate('')->willReturn(false);
        $this->emailValidator->validate('jean@gmail.com')->willReturn(true);
        $this->emailValidator->validate('nicolas@gmail.com')->willReturn(true);
        $this->emailValidator->validate('martine@gmail.com')->willReturn(true);
        $this->emailValidator->validate('julie@gmail.com')->willReturn(true);
        $this->emailValidator->validate('zinedine@example.net')->willReturn(true);

        // Add sheet for "User already exists in DB" for julie@gmail.com
        $sheet1 = new Sheet(
            $event->reveal(),
            $type->reveal(),
            [],
            $userAlreadyExists->reveal(),
            $this->dateTime
        );
        $sheet1->setImported(true);
        $sheet1->setTitle('User already exists in DB');

        $participant1 = new Participant(
            $sheet1,
            $userAlreadyExists->reveal(),
            [],
            false,
            $this->dateTime
        );
        $participant1->setImported(true);

        $sheet1->setRegistrationData(
            ['company' => ['text' => 'User already exists in DB'], 'country' => ['country' => 'FR']]
        );

        $this->sheetRepository
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

        $this->userEventRepository
            ->add(new UserEvent($userAlreadyExists->reveal(), $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $taggedData1 = [
            'participant_firstname' => 'Julie',
            'participant_lastname' => 'KL',
            'participant_phone' => '+33666667700',
            'sheet_organization' => 'User already exists in DB',
        ];
        $this->synchronizer
            ->set($templateData->setTaggedData($taggedData1), $userAlreadyExists->reveal())
            ->shouldBeCalled()
        ;

        // Add sheet for "Ma Petite Tribu" for jean@gmail.com
        $user2 = new User('jean@gmail.com', '', '', 'fr');
        $this->userRepository->add($user2)->shouldBeCalled();

        $sheet2 = new Sheet(
            $event->reveal(),
            $type->reveal(),
            ['sheetTitle' => ['text' => 'Ma Petite Tribu']],
            $user2,
            $this->dateTime
        );
        $sheet2->setImported(true);
        $sheet2->setTitle('Ma Petite Tribu');

        $participant2 = new Participant(
            $sheet2,
            $user2,
            [],
            false,
            $this->dateTime
        );
        $participant2->setImported(true);

        $sheet2->setRegistrationData(['company' => ['text' => 'Ma Petite Tribu'], 'country' => ['country' => 'FR']]);
        $this->sheetRepository
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
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant1)
            ->shouldBeCalled()
        ;

        $callBackParticipant2 = Argument::that(
            static function (Participant $participant) {
                return 'Ma Petite Tribu' === $participant->getSheet()->getTitle()
                    && 'jean@gmail.com' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );

        $this->participantRepository
            ->add($callBackParticipant2)
            ->shouldBeCalled()
        ;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant2)
            ->shouldBeCalled()
        ;

        $this->userEventRepository
            ->add(new UserEvent($user2, $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $taggedData2 = [
            'participant_firstname' => 'Jean',
            'participant_lastname' => 'CD',
            'participant_phone' => '+33666667788',
            'sheet_organization' => 'Ma Petite Tribu',
        ];
        $this->synchronizer->set($templateData->setTaggedData($taggedData2), $user2)->shouldBeCalled();

        $this->participantRepository
            ->getParticipantEmailsForEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                1 => [
                    'email' => 'email-not-used@gmail.com',
                ]
            ])
        ;

        $this->participantRepository
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
                    $this->participantRepository->reveal(),
                    $this->userRepository->reveal(),
                    $this->sheetRepository->reveal(),
                    $this->userEventRepository->reveal(),
                    $this->groupRepository->reveal(),
                    $this->templateDataFactory->reveal(),
                    $this->emailValidator->reveal(),
                    $this->synchronizer->reveal(),
                    $participantLogger,
                    $this->participantOfSheetWithPackageParticipantAndPlanningDisabled->reveal(),
                    $this->dateTime
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
                'mappings' => $mapping,
                'event' => $event->reveal(),
                'type' => $type->reveal(),
                'locale' => $locale,
                'allowMultiSheet' => false,
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

    public function testDenormalizeMultiSheet(): void
    {
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $locale = 'fr';
        $filename = __DIR__ . '/import_participants_multi_sheet.csv';

        $existingUserWithoutGroup = $this->prophesize(User::class);
        $existingUserWithoutGroup->getEmail()->shouldBeCalled()->willReturn('existing-user-without-group@example.net');

        $existingUserWithGroup = $this->prophesize(User::class);
        $existingUserWithGroup->getEmail()->shouldBeCalled()->willReturn('existing-user-with-group@example.net');


        $this->userRepository
            ->findByEmail('existing-user-without-group@example.net')
            ->shouldBeCalled()
            ->willReturn($existingUserWithoutGroup->reveal())
        ;
        $this->userRepository
            ->findByEmail('existing-user-with-group@example.net')
            ->shouldBeCalled()
            ->willReturn($existingUserWithGroup->reveal())
        ;

        $this->userRepository
            ->findByEmail('new-user-1@example.net')
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->userRepository
            ->findByEmail('new-user-2@example.net')
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $errorMessages = [
            'validators.admin.sheet.import_participant.error.country',
            'validators.admin.sheet.participant_import.email.error',
            'validators.admin.sheet.participant_import.email.exist.error',
            'validators.admin.sheet.participant_import.gender.error',
            'validators.admin.sheet.participant_import.nomenclature.error',
            'validators.admin.sheet.participant_import.telephone.error',
        ];

        foreach ($errorMessages as $errorMessage) {
            $this->translatorAdapter->trans($errorMessage, [], 'validators', $locale)->willReturn($errorMessage);
        }

        $participantLogger = new ParticipantImportLogger($this->translatorAdapter->reveal());

        $this->sheetRepository
            ->getOwnerEmails($event->reveal())
            ->shouldNotBeCalled()
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
            ['tags' => ['sheet_title', 'sheet_data']],
            'fr',
            'fr'
        );

        $block->addChild(1, 'firstname', $firstname);
        $block->addChild(1, 'lastname', $lastname);
        $block->addChild(1, 'company', $company);
        $templateData->addChild(0, 'block', $block);
        $sheetTemplateData = new TemplateData('root', [], 'fr', 'fr');

        $this->templateDataFactory
            ->createRegistrationFromType($type, $locale)
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;
        $this->templateDataFactory
            ->createSheetTemplateFromType($type, $locale)
            ->shouldBeCalled()
            ->willReturn($sheetTemplateData)
        ;

        $mapping = [
            'first_anme' => 'lastname',
            'last_name' => 'firstname',
            'sheet_title' => 'company',
            'email' => 'participant_import.field.mail',
            'group_title' => 'participant_import.field.group_title'
        ];

        $this->emailValidator->validate('existing-user-without-group@example.net')->willReturn(true);
        $this->emailValidator->validate('existing-user-with-group@example.net')->willReturn(true);
        $this->emailValidator->validate('new-user-1@example.net')->willReturn(true);
        $this->emailValidator->validate('new-user-2@example.net')->willReturn(true);

        // Add sheet for existing user without group and set group to it
        $existingSheetWithoutGroup = SheetFactory::create(
            $event->reveal(),
            $existingUserWithoutGroup->reveal()
        );
        $existingSheetWithoutGroup->setTitle('previous sheet');

        $newGroup = new Sheet\Group(
            $event->reveal(),
            $existingUserWithoutGroup->reveal(),
            'New Group',
            true,
            $this->dateTime,
            null
        );

        $this->groupRepository->add($newGroup)->shouldBeCalled();
        $this->sheetRepository->set(Argument::that(
            static function (Sheet $sheet) {
                return $sheet->getTitle() === 'previous sheet'
                    && $sheet->hasGroup() === true
                ;
            }
        ))->shouldBeCalled();

        $this->sheetRepository
            ->getSheetsByUserAndEvent($existingUserWithoutGroup->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$existingSheetWithoutGroup])
        ;
        $sheet1 = new Sheet(
            $event->reveal(),
            $type->reveal(),
            [],
            $existingUserWithoutGroup->reveal(),
            $this->dateTime
        );
        $sheet1->setGroup($newGroup);
        $sheet1->setImported(true);
        $sheet1->setTitle('EDM');

        $participant1 = new Participant(
            $sheet1,
            $existingUserWithoutGroup->reveal(),
            [],
            false,
            $this->dateTime
        );
        $participant1->setImported(true);

        $sheet1->setRegistrationData(
            ['company' => ['text' => 'EDM']]
        );

        $this->sheetRepository
            ->add(
                Argument::that(
                    static function (Sheet $sheet) {
                        return 'EDM' === $sheet->getTitle()
                            && $sheet->isImported()
                            && $sheet->hasGroup()
                            && $sheet->getGroupTitle() === 'New Group'
                        ;
                    }
                )
            )
            ->shouldBeCalled()
        ;

        $this->userEventRepository
            ->add(new UserEvent($existingUserWithoutGroup->reveal(), $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $taggedData1 = [
            'participant_firstname' => 'Élisabeth',
            'participant_lastname' => 'De Monnier',
            'sheet_title' => 'EDM',
        ];
        $this->synchronizer
            ->set($templateData->setTaggedData($taggedData1), $existingUserWithoutGroup->reveal())
            ->shouldBeCalled()
        ;

        // Add sheet for existing user with group
        $existingSheetWithGroup = $this->prophesize(Sheet::class);
        $existingSheetWithGroup->hasGroup()->shouldBeCalled()->willReturn(true);
        $existingGroup = $this->prophesize(Sheet\Group::class);
        $existingSheetWithGroup->getGroup()->shouldBeCalled()->willReturn($existingGroup);

        $this->sheetRepository
            ->getSheetsByUserAndEvent($existingUserWithGroup->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn([$existingSheetWithGroup->reveal()])
        ;

        $sheet2 = new Sheet(
            $event->reveal(),
            $type->reveal(),
            [],
            $existingUserWithGroup->reveal(),
            $this->dateTime
        );
        $sheet2->setGroup($existingGroup->reveal());
        $sheet2->setImported(true);
        $sheet2->setTitle('DH');

        $participant2 = new Participant(
            $sheet2,
            $existingUserWithGroup->reveal(),
            [],
            false,
            $this->dateTime
        );
        $participant2->setImported(true);

        $sheet2->setRegistrationData(['company' => ['text' => 'DH']]);
        $this->sheetRepository
            ->add(
                Argument::that(
                    static function (Sheet $sheet) {
                        return 'DH' === $sheet->getTitle()
                            && $sheet->isImported()
                            && $sheet->hasGroup() === true
                        ;
                    }
                )
            )
            ->shouldBeCalled()
        ;

        $callBackParticipant1 = Argument::that(
            static function (Participant $participant) {
                return 'EDM' === $participant->getSheet()->getTitle()
                    && 'existing-user-without-group@example.net' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant1)
            ->shouldBeCalled()
        ;

        $callBackParticipant2 = Argument::that(
            static function (Participant $participant) {
                return 'DH' === $participant->getSheet()->getTitle()
                    && 'existing-user-with-group@example.net' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );

        $this->participantRepository
            ->add($callBackParticipant2)
            ->shouldBeCalled()
        ;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant2)
            ->shouldBeCalled()
        ;

        $this->userEventRepository
            ->add(new UserEvent($existingUserWithGroup->reveal(), $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $taggedData2 = [
            'participant_firstname' => 'Daniel',
            'participant_lastname' => 'Hamel',
            'sheet_title' => 'DH',
        ];
        $this->synchronizer->set(
            $templateData->setTaggedData($taggedData2),
            $existingUserWithGroup->reveal()
        )->shouldBeCalled();

        $this->participantRepository
            ->getParticipantEmailsForEvent($event->reveal())
            ->shouldNotBeCalled()
        ;

        $this->participantRepository
            ->add(Argument::that(static function(Participant $participant) {
                return $participant->getEmail() === 'existing-user-without-group@example.net'
                    || $participant->getEmail() === 'existing-user-with-group@example.net'
                ;
            }))
            ->shouldBeCalled()
        ;

        // Add sheet for new user without creating a group
        $newUser = new User('new-user-1@example.net', '', '', 'fr');
        $newUser->setAccount(new User\Account());

        $this->userRepository->add($newUser)->shouldBeCalled();

        $this->sheetRepository
            ->getSheetsByUserAndEvent($newUser, $event->reveal())
            ->shouldNotBeCalled()
        ;

        $sheet3 = new Sheet(
            $event->reveal(),
            $type->reveal(),
            [],
            $newUser,
            $this->dateTime
        );
        $sheet3->setImported(true);
        $sheet3->setTitle('Lion SA');

        $participant3 = new Participant(
            $sheet3,
            $newUser,
            [],
            false,
            $this->dateTime
        );
        $participant3->setImported(true);

        $sheet3->setRegistrationData(['company' => ['text' => 'Lion SA']]);
        $this->sheetRepository
            ->add(
                Argument::that(
                    static function (Sheet $sheet) {
                        return 'Lion SA' === $sheet->getTitle()
                            && $sheet->isImported()
                            && $sheet->hasGroup() === false
                            ;
                    }
                )
            )
            ->shouldBeCalled()
        ;

        $callBackParticipant3 = Argument::that(
            static function (Participant $participant) {
                return 'Lion SA' === $participant->getSheet()->getTitle()
                    && 'new-user-1@example.net' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );

        $this->participantRepository
            ->add($callBackParticipant3)
            ->shouldBeCalled()
        ;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant3)
            ->shouldBeCalled()
        ;

        $this->userEventRepository
            ->add(new UserEvent($newUser, $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $taggedData3 = [
            'participant_firstname' => 'Alix',
            'participant_lastname' => 'Vincent',
            'sheet_title' => 'Lion SA',
        ];
        $this->synchronizer->set(
            $templateData->setTaggedData($taggedData3),
            $newUser
        )->shouldBeCalled();

        // Add 3 sheet for new user with creating a group
        $newUser2 = new User('new-user-2@example.net', '', '', 'fr');
        $newUser2->setAccount(new User\Account());

        $this->userRepository->add($newUser2)->shouldBeCalled();

        $newGroup2 = new Sheet\Group(
            $event->reveal(),
            $newUser2,
            'Super',
            true,
            $this->dateTime,
            null
        );

        $this->groupRepository->add($newGroup2)->shouldBeCalled();

        $this->sheetRepository
            ->getSheetsByUserAndEvent($newUser2, $event->reveal())
            ->shouldNotBeCalled()
        ;

        $sheet4 = new Sheet(
            $event->reveal(),
            $type->reveal(),
            [],
            $newUser2,
            $this->dateTime
        );
        $sheet5 = new Sheet(
            $event->reveal(),
            $type->reveal(),
            [],
            $newUser2,
            $this->dateTime
        );
        $sheet6 = new Sheet(
            $event->reveal(),
            $type->reveal(),
            [],
            $newUser2,
            $this->dateTime
        );
        $sheet4->setImported(true);
        $sheet5->setImported(true);
        $sheet6->setImported(true);
        $sheet4->setTitle('Super SA');
        $sheet5->setTitle('Super SARL');
        $sheet6->setTitle('Super SCOP');

        $participant4 = new Participant(
            $sheet4,
            $newUser2,
            [],
            false,
            $this->dateTime
        );
        $participant5 = new Participant(
            $sheet5,
            $newUser2,
            [],
            false,
            $this->dateTime
        );
        $participant6 = new Participant(
            $sheet6,
            $newUser2,
            [],
            false,
            $this->dateTime
        );
        $participant4->setImported(true);
        $participant5->setImported(true);
        $participant6->setImported(true);

        $sheet3->setRegistrationData(['company' => ['text' => 'Lion SA']]);
        $this->sheetRepository
            ->add(
                Argument::that(
                    static function (Sheet $sheet) {
                        return 'Super SA' === $sheet->getTitle()
                            && $sheet->isImported()
                            && $sheet->hasGroup() === false
                        ;
                    }
                )
            )
            ->shouldBeCalled()
        ;
        $this->sheetRepository
            ->add(
                Argument::that(
                    static function (Sheet $sheet) {
                        return 'Super SARL' === $sheet->getTitle()
                            && $sheet->isImported()
                            && $sheet->hasGroup() === true
                            ;
                    }
                )
            )
            ->shouldBeCalled()
        ;
        $this->sheetRepository
            ->add(
                Argument::that(
                    static function (Sheet $sheet) {
                        return 'Super SCOP' === $sheet->getTitle()
                            && $sheet->isImported()
                            && $sheet->hasGroup() === true
                            ;
                    }
                )
            )
            ->shouldBeCalled()
        ;
        $this->sheetRepository->set(
            Argument::that(
                static function (Sheet $sheet) {
                    return 'Super SA' === $sheet->getTitle()
                        && $sheet->isImported()
                        && $sheet->hasGroup() === true
                    ;
                }
            )
        )->shouldBeCalled();

        $callBackParticipant4 = Argument::that(
            static function (Participant $participant) {
                return 'Super SA' === $participant->getSheet()->getTitle()
                    && 'new-user-2@example.net' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );
        $callBackParticipant5 = Argument::that(
            static function (Participant $participant) {
                return 'Super SARL' === $participant->getSheet()->getTitle()
                    && 'new-user-2@example.net' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );
        $callBackParticipant6 = Argument::that(
            static function (Participant $participant) {
                return 'Super SCOP' === $participant->getSheet()->getTitle()
                    && 'new-user-2@example.net' === $participant->getUser()->getEmail()
                    && $participant->isImported();
            }
        );

        $this->participantRepository
            ->add($callBackParticipant4)
            ->shouldBeCalled()
        ;
        $this->participantRepository
            ->add($callBackParticipant5)
            ->shouldBeCalled()
        ;
        $this->participantRepository
            ->add($callBackParticipant6)
            ->shouldBeCalled()
        ;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant4)
            ->shouldBeCalled()
        ;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant5)
            ->shouldBeCalled()
        ;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled
            ->handle($callBackParticipant6)
            ->shouldBeCalled()
        ;

        $this->userEventRepository
            ->add(new UserEvent($newUser2, $event->reveal(), $type->reveal()))
            ->shouldBeCalled()
        ;
        $taggedData4 = [
            'participant_firstname' => 'Véronique',
            'participant_lastname' => 'Joly',
            'sheet_title' => 'Super SA',
        ];
        $taggedData5 = [
            'participant_firstname' => 'Véronique',
            'participant_lastname' => 'Joly',
            'sheet_title' => 'Super SARL',
        ];
        $taggedData6 = [
            'participant_firstname' => 'Véronique',
            'participant_lastname' => 'Joly',
            'sheet_title' => 'Super SCOP',
        ];
        $this->synchronizer->set(
            $templateData->setTaggedData($taggedData4),
            $newUser2
        )->shouldBeCalled();
        $this->synchronizer->set(
            $templateData->setTaggedData($taggedData5),
            $newUser2
        )->shouldBeCalled();
        $this->synchronizer->set(
            $templateData->setTaggedData($taggedData6),
            $newUser2
        )->shouldBeCalled();


        // Configure Serializer
        $serializer = new Serializer(
            [
                new ParticipantDenormalizer(
                    $this->participantRepository->reveal(),
                    $this->userRepository->reveal(),
                    $this->sheetRepository->reveal(),
                    $this->userEventRepository->reveal(),
                    $this->groupRepository->reveal(),
                    $this->templateDataFactory->reveal(),
                    $this->emailValidator->reveal(),
                    $this->synchronizer->reveal(),
                    $participantLogger,
                    $this->participantOfSheetWithPackageParticipantAndPlanningDisabled->reveal(),
                    $this->dateTime
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
                'mappings' => $mapping,
                'event' => $event->reveal(),
                'type' => $type->reveal(),
                'locale' => $locale,
                'allowMultiSheet' => true,
            ]
        );

        $expected = [
            'existing_participations' => 0,
            'file_participations' => 6,
            'created_sheets' => 6,
            'created_users' => 2,
            'import_errors' => [],
        ];

        $this->assertEquals($expected, $importLogger->toArray());
    }
}
