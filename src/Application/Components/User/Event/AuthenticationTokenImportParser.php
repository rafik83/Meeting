<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\User\Event;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\ValidatorAdapter;
use Symfony\Component\Validator\ConstraintViolationList;

class AuthenticationTokenImportParser
{
    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ValidatorAdapter */
    private $validator;

    /** @var TranslatorAdapter */
    private $translator;

    /** @var string */
    private $importDir;

    public function __construct(
        SerializerAdapterInterface $serializer,
        SheetRepositoryInterface $sheetRepository,
        ParticipantRepositoryInterface $participantRepository,
        ValidatorAdapter $validator,
        TranslatorAdapter $translator,
        string $importDir
    ) {
        $this->serializer = $serializer;
        $this->sheetRepository = $sheetRepository;
        $this->participantRepository = $participantRepository;
        $this->validator = $validator;
        $this->translator = $translator;
        $this->importDir = $importDir;
    }

    public function parse(Event $event, File $importedFile): iterable
    {
        $authenticationTokenImports = $this->serializer->deserialize(
            file_get_contents($this->importDir . $importedFile->getPath()),
            AuthenticationTokenImport::class,
            'csv',
            [
                'csv_delimiter' => ';',
                'event' => $event,
            ]
        );

        $importedEmail = [];

        /** @var AuthenticationTokenImport $authenticationTokenImport */
        foreach ($authenticationTokenImports as $authenticationTokenImport) {
            // In case of error(s) on denormalizer, skip other validations.
            if ($authenticationTokenImport->hasError()) {
                continue;
            }

            $authenticationTokenImportView = $authenticationTokenImport->authenticationTokenImportView;

            if (isset($importedEmail[$authenticationTokenImportView->email])) {
                $authenticationTokenImport->addError(
                    $this->translator->trans('validators.authentication_token.csv.email_already_imported', [], 'validators')
                );
            }

            /** @var ConstraintViolationList $emailValidations */
            $emailValidations = $this->validator->validate($authenticationTokenImportView->email, ValidatorInterface::VALIDATOR_EMAIL_TYPE);
            if ($emailValidations->count() > 0) {
                foreach ($emailValidations as $validation) {
                    $authenticationTokenImport->addError($validation->getMessage());
                }

                continue;
            }

            if (false === $this->canUserAccessToEvent($event, $authenticationTokenImportView->email)) {
                $authenticationTokenImport->addError(
                    $this->translator->trans('validators.authentication_token.csv.unknown_email', [], 'validators')
                );
            }

            $importedEmail[$authenticationTokenImportView->email] = true;
        }

        return $authenticationTokenImports;
    }

    private function canUserAccessToEvent(Event $event, string $importedEmail): bool
    {
        $ownerEmails = $this->sheetRepository->getOwnerEmails($event);
        $participantEmails = $this->participantRepository->getParticipantEmailsForEvent($event);

        foreach (array_merge($ownerEmails, $participantEmails) as $email) {
            if (strtolower($email['email']) === strtolower($importedEmail)) {
                return true;
            }
        }

        return false;
    }
}
