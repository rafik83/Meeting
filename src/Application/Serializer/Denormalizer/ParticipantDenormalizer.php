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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\ObjectValidatorNotExistException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\ContentObjectInterface;
use Proximum\Vimeet\Domain\Template\Validator\Error\EmailError;
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
     * ParticipantDenormalizer constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param SheetRepositoryInterface       $sheetRepository
     * @param UserRepositoryInterface        $userRepository
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
        TemplateDataFactory $templateDataFactory,
        EmailValidator $emailValidator,
        Synchronizer $synchronizer,
        ParticipantImportLogger $importLogger,
        \DateTimeInterface $dateTime
    ) {
        $this->participantRepository = $participantRepository;
        $this->templateDataFactory   = $templateDataFactory;
        $this->synchronizer          = $synchronizer;
        $this->dateTime              = $dateTime;
        $this->userRepository        = $userRepository;
        $this->emailValidator        = $emailValidator;
        $this->importLogger          = $importLogger;
        $this->sheetRepository       = $sheetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $participants = $this->participantRepository->findByEvent($context['event']);

        $this->importLogger->init(count($participants), count($data));

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

            $email = $row[$mappedMailCsvColumn];

            if ($this->emailValidator->validate($email) === false) {
                $this->importLogger->addError($key, new EmailError($email, true), $email, $context['locale']);
                continue;
            }

            if ($this->isAlreadyParticipant($email, $participants)) {
                continue;
            }

            try {
                list($sheetData, $participantData) = $this->handleRow($row, $mappingGuesser, $registrationTemplate, $context);

                $this->createEntities($context, $email, $users, $sheetData, $participantData, $registrationTemplate);
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
        return $format === self::FORMAT;
    }

    /**
     * @param string        $email
     * @param Participant[] $participants
     *
     * @return bool
     */
    private function isAlreadyParticipant($email, array $participants)
    {
        foreach ($participants as $participant) {
            if ($participant->getUser()->getEmail() === $email) {
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
    private function hasUserAccount($email, array $users)
    {
        foreach ($users as $user) {
            if ($user->getEmail() === $email) {
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
     * @return array
     * @throws InvalidObjectContentException
     */
    private function handleRow(
        array $row,
        MappingGuesser $mappingGuesser,
        TemplateData $registrationTemplate,
        array $context
    ) {
        $sheetData       = [];
        $participantData = [];

        foreach ($row as $key => $column) {
            $registrationObjectKey = $mappingGuesser->getMappedOutKey($key);

            if ($registrationObjectKey === false
                || $registrationObjectKey === ParticipantImportTag::REGISTRATION_FIELD_MAIL
            ) {
                continue;
            }

            $templateObject = $registrationTemplate->getObject($registrationObjectKey);

            if ($templateObject instanceof ContentObjectInterface) {
                try {
                    $validator      = ObjectValidatorFactory::create($templateObject);
                    $validatorError = $validator->validate($column, ['locale' => $context['locale']]);

                    if (!$validatorError->hasError()) {
                        $registrationTemplate->clear();
                        $templateObject->setContentValue($column);

                        if ($templateObject->hasTag(Tag::SHEET_DATA)) {
                            $sheetData = array_merge($sheetData, [
                                $registrationObjectKey => $templateObject->getData()
                            ]);
                        }

                        if ($templateObject->hasTag(Tag::PARTICIPANT_DATA)) {
                            $participantData = array_merge($participantData, [
                                $registrationObjectKey => $templateObject->getData()
                            ]);
                        }

                    } else {
                        throw new InvalidObjectContentException($validatorError);
                    }
                } catch (ObjectValidatorNotExistException $exception) {
                    $registrationTemplate->clear();
                    $templateObject->setContentValue($column);

                    if ($templateObject->hasTag(Tag::SHEET_DATA)) {
                        $sheetData = array_merge($sheetData, [
                            $registrationObjectKey => $templateObject->getData()
                        ]);
                    }

                    if ($templateObject->hasTag(Tag::PARTICIPANT_DATA)) {
                        $participantData = array_merge($participantData, [
                            $registrationObjectKey => $templateObject->getData()
                        ]);
                    }

                }
            }
        }

        return [$sheetData, $participantData];
    }

    /**
     * @param array        $context
     * @param string       $email
     * @param User[]       $users
     * @param array        $sheetData
     * @param array        $participantData
     * @param TemplateData $registrationTemplate
     */
    private function createEntities(
        array $context,
        $email,
        $users,
        $sheetData,
        $participantData,
        TemplateData $registrationTemplate
    ) {
        if (($user = $this->hasUserAccount($email, $users)) === false) {
            $user = new User($email, '', '', $context['locale']);
            $user->setAccount(new User\Account());

            $this->userRepository->add($user);
            $this->importLogger->userImported();
        }

        $sheet = new Sheet($context['event'], $context['type'], [], $user, $this->dateTime);

        $participant = new Participant($sheet, $user, [], false);
        $participant->setImported(true);

        $sheet->setRegistrationData($sheetData);
        $participant->setData($participantData);

        $this->sheetRepository->add($sheet);
        $this->participantRepository->add($participant);

        $sheet->addParticipant($participant); // required to have participant in array sheet array collection

        $this->importLogger->sheetImported($sheet);

        $this->synchronizer->set($registrationTemplate, $user);
    }
}
