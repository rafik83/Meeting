<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Serializer\Denormalizer;

use Proximum\Vimeet\Application\Components\Import\MappingGuesser;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Serializer\Denormalizer\Exception\InvalidObjectContentException;
use Proximum\Vimeet\Domain\Account\EmailValidator;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Email\EmailHelper;
use Proximum\Vimeet\Domain\Helper\StringHelper;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\UserEvent;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\ObjectValidatorNotExistException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\Validator\Error\EmailError;
use Proximum\Vimeet\Domain\Template\Validator\Error\EmailExistError;
use Proximum\Vimeet\Domain\Template\Validator\ObjectValidatorFactory;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ParticipantDenormalizer implements DenormalizerInterface
{
    const FORMAT = 'csv';

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var TemplateDataFactory
     */
    private $templateDataFactory;

    /**
     * @var Synchronizer
     */
    private $synchronizer;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @var ParticipantImportLogger
     */
    private $importLogger;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var UserEventRepositoryInterface
     */
    private $userEventRepository;

    /**
     * ParticipantDenormalizer constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param UserRepositoryInterface        $userRepository
     * @param SheetRepositoryInterface       $sheetRepository
     * @param UserEventRepositoryInterface   $userEventRepository
     * @param TemplateDataFactory            $templateDataFactory
     * @param EmailValidator                 $emailValidator
     * @param Synchronizer                   $synchronizer
     * @param ParticipantImportLogger        $importLogger
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        UserRepositoryInterface $userRepository,
        SheetRepositoryInterface $sheetRepository,
        UserEventRepositoryInterface $userEventRepository,
        TemplateDataFactory $templateDataFactory,
        EmailValidator $emailValidator,
        Synchronizer $synchronizer,
        ParticipantImportLogger $importLogger,
        \DateTimeInterface $dateTime
    ) {
        $this->participantRepository = $participantRepository;
        $this->userRepository        = $userRepository;
        $this->sheetRepository       = $sheetRepository;
        $this->userEventRepository   = $userEventRepository;
        $this->templateDataFactory   = $templateDataFactory;
        $this->emailValidator        = $emailValidator;
        $this->synchronizer          = $synchronizer;
        $this->importLogger          = $importLogger;
        $this->dateTime              = $dateTime;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $sheets = $this->sheetRepository->getByEventWithParticipantsAndOwner($context['event']);
        $participants = [];

        foreach ($sheets as $sheet) {
            foreach ($sheet->getParticipants()->toArray() as $participant) {
                $participants[] = $participant;
            }
        }

        $this->importLogger->init(count($data));

        $mappingGuesser = new MappingGuesser($context['mappings']);

        $mappedMailCsvColumn = $mappingGuesser->getMappedInKey(ParticipantImportTag::REGISTRATION_FIELD_MAIL);

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType(
            $context['type'],
            $context['locale']
        );

        $users = $this->userRepository->all();

        foreach ($data as $key => $row) {
            if (!array_key_exists($mappedMailCsvColumn, $row)) {
                continue;
            }

            $email = StringHelper::trimSpacesAndNonBreakSpaces($row[$mappedMailCsvColumn]);

            if ($this->emailValidator->validate($email) === false) {
                $this->importLogger->addError($key, new EmailError($email, true), $email, $context['locale']);
                continue;
            }

            if ($this->importLogger->isImported($email)) {
                $this->importLogger->addError($key, new EmailExistError($email, true), $email, $context['locale']);
                continue;
            }

            if ($this->isAlreadyOwner($email, $sheets) || $this->isAlreadyParticipant($email, $participants)) {
                $this->importLogger->existingParticipations();
                continue;
            }

            try {
                list($sheetData, $participantData, $sheetTitle) = $this->handleRow(
                    $row,
                    $mappingGuesser,
                    $registrationTemplate,
                    $context
                );

                $this->createEntities(
                    $context,
                    $email,
                    $users,
                    $sheetTitle,
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
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Participant::class && $format === self::FORMAT;
    }

    /**
     * @param string        $email
     * @param Participant[] $participants
     *
     * @return bool
     */
    private function isAlreadyParticipant($email, &$participants)
    {
        foreach ($participants as $participant) {
            if (strtolower($participant->getUser()->getEmail()) === strtolower($email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string  $email
     * @param Sheet[] $sheets
     *
     * @return bool
     */
    private function isAlreadyOwner($email, &$sheets)
    {
        foreach ($sheets as $sheet) {
            if (strtolower($sheet->getOwner()->getEmail()) === strtolower($email)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $email
     * @param User[] $users
     *
     * @return User|false
     */
    private function hasUserAccount($email, &$users)
    {
        foreach ($users as $user) {
            if (strtolower($user->getEmail()) === strtolower($email)) {
                return $user;
            }
        }

        return false;
    }

    /**
     * @param array          $row
     * @param MappingGuesser $mappingGuesser
     * @param TemplateData   $registrationTemplate
     * @param array          $context
     *
     * @return array of sheetData, participantData and sheetTitle
     * @throws InvalidObjectContentException
     * @throws \Exception
     */
    private function handleRow(
        array $row,
        MappingGuesser $mappingGuesser,
        TemplateData $registrationTemplate,
        array $context
    ) {
        $sheetData       = [];
        $participantData = [];
        $sheetTitle      = '';

        // clear previous data before process current imported participant row
        $registrationTemplate->clear();

        foreach ($row as $key => $column) {
            $column = trim($column);
            $registrationObjectKey = $mappingGuesser->getMappedOutKey($key);

            if ($registrationObjectKey === false
                || $registrationObjectKey === ParticipantImportTag::REGISTRATION_FIELD_MAIL
            ) {
                continue;
            }

            $templateObject = $registrationTemplate->getObject($registrationObjectKey);

            if (!$templateObject instanceof ContentObjectInterface) {
                continue;
            }

            try {
                if ($templateObject instanceof TemplateObject\Telephone) {
                    $column = $this->denormalizerPhoneNumber($column);
                }

                $validator      = ObjectValidatorFactory::create($templateObject);
                $validatorError = $validator->validate($column, [
                    'locale' => $context['locale'],
                    'object' => $templateObject,
                ]);

                if (!$validatorError->hasError()) {
                    if ($templateObject instanceof TemplateObject\Nomenclature) {
                        $nomenclatureKey = $templateObject->getKeyForLabel($column, $context['locale']);
                        $templateObject->setContentValue($nomenclatureKey);
                    } else {
                        $templateObject->setContentValue($column);
                    }

                    $this->dispatchTemplateData($templateObject, $sheetData, $participantData, $registrationObjectKey);
                } else {
                    throw new InvalidObjectContentException($validatorError);
                }
            } catch (ObjectValidatorNotExistException $exception) {
                // if not validator defined, set data without check if valid
                if ($templateObject instanceof TemplateObject\Nomenclature) {
                    $nomenclatureKey = $templateObject->getKeyForLabel($column, $context['locale']);
                    $templateObject->setContentValue($nomenclatureKey);
                } else {
                    $templateObject->setContentValue($column);
                }

                $this->dispatchTemplateData($templateObject, $sheetData, $participantData, $registrationObjectKey);
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

        return [$sheetData, $participantData, $sheetTitle];
    }

    /**
     * @param $phone
     *
     * @return string
     */
    private function denormalizerPhoneNumber($phone)
    {
        return preg_replace("/(\\'+)|(\\s)+/", '', $phone);
    }

    /**
     * @param array        $context
     * @param string       $email
     * @param User[]       $users
     * @param string       $sheetTitle
     * @param array        $sheetData
     * @param array        $participantData
     * @param TemplateData $registrationTemplate
     */
    private function createEntities(
        array $context,
        $email,
        &$users,
        $sheetTitle,
        $sheetData,
        $participantData,
        TemplateData $registrationTemplate
    ) {
        $user = $this->hasUserAccount($email, $users);

        if ($user === false) {
            $user = new User($email, '', '', $context['locale']);
            $user->setAccount(new User\Account());

            $this->userRepository->add($user);
            $this->importLogger->userImported($user);
        }

        $sheet = new Sheet($context['event'], $context['type'], [], $user, $this->dateTime);
        $sheet->setImported(true);
        $sheetTitle = !empty(trim($sheetTitle)) ? $sheetTitle : $user->getFullname();
        $sheetTitle = !empty(trim($sheetTitle)) ? $sheetTitle : $user->getEmail();
        $sheet->setTitle($sheetTitle);
        $sheet->setRegistrationData($sheetData);
        $this->sheetRepository->add($sheet);

        $participant = new Participant($sheet, $user, $participantData, false);
        $participant->setImported(true);
        $this->participantRepository->add($participant);
        $sheet->addParticipant($participant); // required to have participant in array sheet array collection

        $this->userEventRepository->add(new UserEvent($user, $context['event'], $context['type']));

        $this->importLogger->sheetImported($sheet);

        $this->synchronizer->set($registrationTemplate, $user);
    }

    /**
     * @param TemplateObject $templateObject
     * @param array          $sheetData
     * @param array          $participantData
     * @param                $registrationObjectKey
     */
    private function dispatchTemplateData(
        TemplateObject $templateObject,
        &$sheetData,
        &$participantData,
        $registrationObjectKey
    ) {
        if ($templateObject->hasTag(Tag::SHEET_DATA)) {
            $sheetData = array_merge($sheetData, [
                $registrationObjectKey => $templateObject->getData(),
            ]);
        }

        if ($templateObject->hasTag(Tag::PARTICIPANT_DATA)) {
            $participantData = array_merge($participantData, [
                $registrationObjectKey => $templateObject->getData(),
            ]);
        }
    }
}
