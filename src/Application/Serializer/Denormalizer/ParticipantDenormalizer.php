<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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

    /**
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
        $event = $context['event'];
        $ownerEmails = $this->sheetRepository->getOwnerEmails($event);
        $participantEmails = $this->participantRepository->getParticipantEmailsForEvent($event);

        // Owners and Participant indexed by email
        $participants = [];
        $owners       = [];

        foreach ($participantEmails as $participantEmail) {
            $participants[strtolower($participantEmail['email'])] = $participantEmail['email'];
        }

        foreach ($ownerEmails as $ownerEmail) {
            $owners[strtolower($ownerEmail['email'])] = $ownerEmail['email'];
        }

        $this->importLogger->init(\count($data));

        $mappingGuesser = new MappingGuesser($context['mappings']);

        $mappedMailCsvColumn = $mappingGuesser->getMappedInKey(ParticipantImportTag::REGISTRATION_FIELD_MAIL);

        $registrationTemplate = $this->templateDataFactory->createRegistrationFromType(
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

            if ($this->importLogger->isImported($email)) {
                $this->importLogger->addError($key, new EmailExistError($email, true), $email, $context['locale']);
                continue;
            }

            if ($this->isAlreadyOwner($email, $owners) || $this->isAlreadyParticipant($email, $participants)) {
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

    /**
     * @param string $email
     *
     * @return User
     */
    private function getUser(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * @param array          $row
     * @param MappingGuesser $mappingGuesser
     * @param TemplateData   $registrationTemplate
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

            if (false === $registrationObjectKey
                || ParticipantImportTag::REGISTRATION_FIELD_MAIL === $registrationObjectKey
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

                $validator = ObjectValidatorFactory::create($templateObject);
                $validatorError = $validator->validate($column, [
                    'locale' => $context['locale'],
                    'object' => $templateObject,
                ]);

                if (!$validatorError->hasError()) {
                    $this->handleColumn(
                        $templateObject,
                        $column,
                        $sheetData,
                        $participantData,
                        $registrationObjectKey,
                        $context['locale']
                    );
                } else {
                    throw new InvalidObjectContentException($validatorError);
                }
            } catch (ObjectValidatorNotExistException $exception) {
                // if not validator defined, set data without check if valid
                $this->handleColumn(
                    $templateObject,
                    $column,
                    $sheetData,
                    $participantData,
                    $registrationObjectKey,
                    $context['locale']
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

        return [$sheetData, $participantData, $sheetTitle];
    }

    private function handleColumn(
        ContentObjectInterface $templateObject,
        $column,
        array &$sheetData,
        array &$participantData,
        $registrationObjectKey,
        $locale
    ): void {
        if ($templateObject instanceof TemplateObject\Nomenclature) {
            $items    = $this->denormalizerNomenclatureItems($column);
            $itemKeys = [];

            foreach ($items as $item) {
                $itemKeys[] = $templateObject->getKeyForLabel($item, $locale);
            }

            $templateObject->setItems($itemKeys);
        } else {
            $templateObject->setContentValue($column);
        }

        $this->dispatchTemplateData($templateObject, $sheetData, $participantData, $registrationObjectKey);
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

    private function denormalizerNomenclatureItems($data): array
    {
        $dataItems = explode(';', $data);

        return array_map(function ($element) {
            return str_replace(TemplateObject\Nomenclature::SEMICOLON_ESCAPE_CHAR, ';', $element);
        }, $dataItems);
    }

    /**
     * @param array        $context
     * @param string       $email
     * @param string       $sheetTitle
     * @param array        $sheetData
     * @param array        $participantData
     * @param TemplateData $registrationTemplate
     */
    private function createEntities(
        array $context,
        $email,
        $sheetTitle,
        $sheetData,
        $participantData,
        TemplateData $registrationTemplate
    ) {
        $user = $this->getUser($email);

        if (!$user instanceof User) {
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

        $participant = new Participant(
            $sheet,
            $user,
            $participantData,
            false
        );
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
    ): void {
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
