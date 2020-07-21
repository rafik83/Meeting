<?php

namespace Proximum\Vimeet\Application\Serializer\Denormalizer;

use Proximum\Vimeet\Application\Components\Import\MappingGuesser;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Exception\InvalidObjectContentException;
use Proximum\Vimeet\Domain\Account\EmailValidator;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Participant\ParticipantOfSheetWithPackageParticipantAndPlanningDisabled;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\ObjectNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\ObjectValidatorNotExistException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\Validator\Error\EmailError;
use Proximum\Vimeet\Domain\Template\Validator\Error\EmailExistError;
use Proximum\Vimeet\Domain\Template\Validator\ObjectValidatorFactory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function count;

class ParticipantDenormalizer implements DenormalizerInterface
{
    private const FORMAT = 'csv';

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var Synchronizer */
    private $synchronizer;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var EmailValidator */
    private $emailValidator;

    /** @var ParticipantImportLogger */
    private $importLogger;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var UserEventRepositoryInterface */
    private $userEventRepository;

    /** @var ParticipantOfSheetWithPackageParticipantAndPlanningDisabled */
    private $participantOfSheetWithPackageParticipantAndPlanningDisabled;

    /** @var User[] indexed by user email */
    private $users = [];

    /** @var Sheet[] indexed by user email */
    private $userSheets = [];

    /** @var array */
    private $userGroupTitles = [];

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        UserEventRepositoryInterface $userEventRepository,
        GroupRepositoryInterface $groupRepository,
        TemplateDataFactory $templateDataFactory,
        EmailValidator $emailValidator,
        Synchronizer $synchronizer,
        ParticipantImportLogger $importLogger,
        ParticipantOfSheetWithPackageParticipantAndPlanningDisabled $participantOfSheetWithPackageParticipantAndPlanningDisabled,
        \DateTimeInterface $dateTime
    ) {
        $this->participantRepository = $participantRepository;
        $this->userRepository = $userRepository;
        $this->sheetRepository = $sheetRepository;
        $this->userEventRepository = $userEventRepository;
        $this->templateDataFactory = $templateDataFactory;
        $this->emailValidator = $emailValidator;
        $this->synchronizer = $synchronizer;
        $this->importLogger = $importLogger;
        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled = $participantOfSheetWithPackageParticipantAndPlanningDisabled;
        $this->dateTime = $dateTime;
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $event = $context['event'];
        $allowMultiSheet = $context['allowMultiSheet'] ?? false;

        // Owners and Participant indexed by email
        $participants = [];
        $owners = [];

        if (!$allowMultiSheet) {
            $participantEmails = $this->participantRepository->getParticipantEmailsForEvent($event);

            foreach ($participantEmails as $participantEmail) {
                $participants[strtolower($participantEmail['email'])] = $participantEmail['email'];
            }

            $ownerEmails = $this->sheetRepository->getOwnerEmails($event);
            foreach ($ownerEmails as $ownerEmail) {
                $owners[strtolower($ownerEmail['email'])] = $ownerEmail['email'];
            }
        }

        $this->importLogger->init(count($data));

        $mappingGuesser = new MappingGuesser($context['mappings']);

        $mappedMailCsvColumn = $mappingGuesser->getMappedInKey(ParticipantImportTag::REGISTRATION_FIELD_MAIL);

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType(
            $context['type'],
            $context['locale']
        );
        $sheetTemplate = $this->templateDataFactory->createSheetTemplateFromType(
            $context['type'],
            $context['locale']
        );

        foreach ($data as $key => $row) {
            if (!array_key_exists($mappedMailCsvColumn, $row)) {
                continue;
            }

            $email = strtolower(StringHelper::trimSpacesAndNonBreakSpaces($row[$mappedMailCsvColumn]));

            if (false === $this->emailValidator->validate($email)) {
                $this->importLogger->addError($key, new EmailError($email, true), $email, $context['locale']);
                continue;
            }

            if (!$allowMultiSheet) {
                if ($this->importLogger->isImported($email)) {
                    $this->importLogger->addError($key, new EmailExistError($email, true), $email, $context['locale']);
                    continue;
                }

                if ($this->isAlreadyOwner($email, $owners) || $this->isAlreadyParticipant($email, $participants)) {
                    $this->importLogger->existingParticipations();
                    continue;
                }
            }

            try {
                [
                    $sheetRegistrationData,
                    $sheetData,
                    $participantData,
                    $sheetTitle,
                    $groupTitle,
                ] = $this->handleRow(
                    $row,
                    $mappingGuesser,
                    $registrationTemplate,
                    $sheetTemplate,
                    $context
                );

                $this->createEntities(
                    $context,
                    $email,
                    $sheetTitle,
                    $groupTitle,
                    $sheetRegistrationData,
                    $sheetData,
                    $participantData,
                    $registrationTemplate
                );
            } catch (InvalidObjectContentException $exception) {
                $this->importLogger->addError(
                    $key,
                    $exception->getValidatorError(),
                    $exception->getValidatorError()->getData(),
                    $context['locale']
                );
            }
        }

        return $this->importLogger;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return Participant::class === $type && self::FORMAT === $format;
    }

    /**
     * @param string        $email
     * @param Participant[] $participants
     *
     * @return bool
     */
    private function isAlreadyParticipant(string $email, array &$participants): bool
    {
        return isset($participants[$email]);
    }

    /**
     * @param string $email
     * @param user[] $owners
     *
     * @return bool
     */
    private function isAlreadyOwner(string $email, array &$owners): bool
    {
        return isset($owners[$email]);
    }

    private function getUser(string $email): ?User
    {
        if (isset($this->users[$email])) {
            return $this->users[$email];
        }

        $user = $this->userRepository->findByEmail($email);
        $this->users[$email] = $user;

        return $user;
    }

    /**
     * @param array          $row
     * @param MappingGuesser $mappingGuesser
     * @param TemplateData   $registrationTemplate
     * @param TemplateData   $sheetTemplate
     * @param array          $context
     *
     * @throws InvalidObjectContentException
     * @throws \Exception
     *
     * @return array of sheetData, participantData and sheetTitle
     */
    private function handleRow(
        array $row,
        MappingGuesser $mappingGuesser,
        TemplateData $registrationTemplate,
        TemplateData $sheetTemplate,
        array $context
    ): array {
        $sheetRegistrationData = [];
        $participantData = [];
        $sheetData = [];
        $sheetTitle = '';
        $groupTitle = null;

        // clear previous data before process current imported participant row
        $registrationTemplate->clear();

        foreach ($row as $key => $column) {
            $column = trim($column);
            $objectKey = $mappingGuesser->getMappedOutKey($key);

            if (false === $objectKey
                || ParticipantImportTag::REGISTRATION_FIELD_MAIL === $objectKey
            ) {
                continue;
            }

            if (ParticipantImportTag::FIELD_GROUP_TITLE === $objectKey) {
                $groupTitle = $column;

                continue;
            }

            $isRegistrationObject = false;
            try {
                $templateObject = $registrationTemplate->getObject($objectKey);
                $isRegistrationObject = true;
            } catch (ObjectNotFoundException $exception) {
                $templateObject = $sheetTemplate->getObject($objectKey);
            }

            if (!$templateObject instanceof ContentObjectInterface) {
                continue;
            }

            try {
                if ($templateObject instanceof TemplateObject\Telephone) {
                    $column = $this->denormalizerPhoneNumber($column);
                }

                $validator = ObjectValidatorFactory::create($templateObject);
                $validatorError = $validator->validate($column, [
                    'locale' => $context['locale'],
                    'object' => $templateObject,
                ]);

                if (!$validatorError->hasError()) {
                    $this->handleColumn(
                        $templateObject,
                        $column,
                        $context['locale']
                    );

                    $this->dispatchTemplateData(
                        $isRegistrationObject,
                        $templateObject,
                        $sheetRegistrationData,
                        $sheetData,
                        $participantData,
                        $objectKey
                    );
                } else {
                    throw new InvalidObjectContentException($validatorError);
                }
            } catch (ObjectValidatorNotExistException $exception) {
                // if not validator defined, set data without check if valid
                $this->handleColumn(
                    $templateObject,
                    $column,
                    $context['locale']
                );

                $this->dispatchTemplateData(
                    $isRegistrationObject,
                    $templateObject,
                    $sheetRegistrationData,
                    $sheetData,
                    $participantData,
                    $objectKey
                );
            }

            if ($templateObject->hasTag(Tag::SHEET_ORGANIZATION) && !empty($templateObject->getContentValue())) {
                $sheetTitle = $column;
            }

            if ('' === $sheetTitle
                && $templateObject->hasTag(Tag::SHEET_TITLE)
                && !empty($templateObject->getContentValue())
            ) {
                $sheetTitle = $column;
            }
        }

        return [
            $sheetRegistrationData,
            $sheetData,
            $participantData,
            $sheetTitle,
            $groupTitle,
        ];
    }

    private function handleColumn(
        ContentObjectInterface $templateObject,
        $column,
        $locale
    ): void {
        if ($templateObject instanceof TemplateObject\Nomenclature) {
            $items = $this->denormalizerNomenclatureItems($column);
            $itemKeys = [];

            foreach ($items as $item) {
                $itemKeys[] = $templateObject->getKeyForLabel($item, $locale);
            }

            $templateObject->setItems($itemKeys);
        } else {
            $templateObject->setContentValue($column);
        }
    }

    private function denormalizerPhoneNumber($phone): string
    {
        return preg_replace("/(\\'+)|(\\s)+/", '', $phone);
    }

    private function denormalizerNomenclatureItems($data): array
    {
        $dataItems = explode(';', $data);

        return array_map(static function ($element) {
            return str_replace(TemplateObject\Nomenclature::SEMICOLON_ESCAPE_CHAR, ';', $element);
        }, $dataItems);
    }

    /**
     * @param array        $context
     * @param string       $email
     * @param string       $sheetTitle
     * @param null|string  $groupTitle
     * @param array        $sheetRegistrationData
     * @param array        $sheetData
     * @param array        $participantData
     * @param TemplateData $registrationTemplate
     */
    private function createEntities(
        array $context,
        $email,
        string $sheetTitle,
        ?string $groupTitle,
        $sheetRegistrationData,
        $sheetData,
        $participantData,
        TemplateData $registrationTemplate
    ): void {
        $event = $context['event'];
        $user = $this->getUser($email);

        if (!$user instanceof User) {
            $user = $this->createUser($email, $context['locale']);
        }

        $group = $this->getGroup(
            $email,
            $user,
            $event,
            $groupTitle,
            $context
        );

        $sheet = new Sheet($event, $context['type'], $sheetData, $user, $this->dateTime);
        $sheet->setImported(true);
        $sheetTitle = !empty(trim($sheetTitle)) ? $sheetTitle : $user->getFullname();
        $sheetTitle = !empty(trim($sheetTitle)) ? $sheetTitle : $user->getEmail();
        $sheet->setTitle($sheetTitle);
        $sheet->setRegistrationData($sheetRegistrationData);

        if ($group instanceof Sheet\Group) {
            $sheet->setGroup($group);
        }

        $this->sheetRepository->add($sheet);

        $participant = new Participant(
            $sheet,
            $user,
            $participantData,
            false,
            $this->dateTime
        );
        $participant->setImported(true);
        $this->participantRepository->add($participant);
        $sheet->addParticipant($participant); // required to have participant in array sheet array collection

        /* @todo to remove ? */
        $this->userEventRepository->add(new UserEvent($user, $context['event'], $context['type']));

        $this->participantOfSheetWithPackageParticipantAndPlanningDisabled->handle($participant);

        $this->importLogger->sheetImported($sheet);

        $this->synchronizer->set($registrationTemplate, $user);

        $this->userSheets[$user->getEmail()][] = $sheet;

        // Keep the group title of the first row with one.
        if (!isset($this->userGroupTitles[$email])) {
            $this->userGroupTitles[$email] = $groupTitle;
        }
    }

    private function createUser(string $email, string $locale): User
    {
        $user = new User($email, '', '', $locale);
        $user->setAccount(new User\Account());

        $this->userRepository->add($user);
        $this->importLogger->userImported($user);

        $this->userSheets[$email] = [];
        $this->users[$email] = $user;

        return $user;
    }

    private function getGroup(
        string $email,
        User $user,
        Event $event,
        ?string $groupTitle,
        array $context
    ): ?Sheet\Group {
        $group = null;
        $allowMultiSheet = $context['allowMultiSheet'] ?? false;

        if (!$allowMultiSheet) {
            return null;
        }

        // Get the user sheets on the first run.
        if (!isset($this->userSheets[$email])) {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);
            $this->userSheets[$email] = $sheets;
        }

        // Existing sheet for user.
        if (!empty($this->userSheets[$email])) {
            $firstSheet = null;

            /** @var Sheet $userSheet */
            foreach ($this->userSheets[$email] as $userSheet) {
                if ($firstSheet === null) {
                    $firstSheet = $userSheet;
                }

                if ($userSheet->hasGroup()) {
                    $group = $userSheet->getGroup();
                    break;
                }
            }

            if (!$group instanceof Sheet\Group) {
                $group = $this->groupRepository->getByUserAndEvent($user, $event);

                if (!$group instanceof Sheet\Group) {
                    if (isset($this->userGroupTitles[$email])) {
                        $groupTitle = $this->userGroupTitles[$email];
                    }

                    $group = new Sheet\Group(
                        $event,
                        $user,
                        $groupTitle,
                        true,
                        $this->dateTime,
                        null
                    );

                    $this->groupRepository->add($group);
                }

                if ($firstSheet instanceof Sheet && !$firstSheet->hasGroup()) {
                    $firstSheet->setGroup($group);
                    $this->sheetRepository->set($firstSheet);
                }
            }
        }

        return $group;
    }

    /**
     * @param bool           $isRegistrationObject
     * @param TemplateObject $templateObject
     * @param array          $sheetRegistrationData
     * @param array          $sheetData
     * @param array          $participantData
     * @param string         $objectKey
     */
    private function dispatchTemplateData(
        bool $isRegistrationObject,
        TemplateObject $templateObject,
        array &$sheetRegistrationData,
        array &$sheetData,
        array &$participantData,
        string $objectKey
    ): void {
        if (false === $isRegistrationObject) {
            $sheetData = array_merge($sheetData, [
                $objectKey => $templateObject->getData(),
            ]);

            return;
        }

        if ($templateObject->hasTag(Tag::SHEET_DATA)) {
            $sheetRegistrationData = array_merge($sheetRegistrationData, [
                $objectKey => $templateObject->getData(),
            ]);
        }

        if ($templateObject->hasTag(Tag::PARTICIPANT_DATA)) {
            $participantData = array_merge($participantData, [
                $objectKey => $templateObject->getData(),
            ]);
        }
    }
}
