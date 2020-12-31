<?php

namespace Proximum\Vimeet\Application\Components\User\Event;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ValidatorInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Infrastructure\Adapter\ValidatorAdapter;
use Symfony\Component\Validator\ConstraintViolationList;

class AuthenticationTokenImportParser
{
    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ValidatorAdapter */
    private $validator;

    /** @var string */
    private $importDir;

    public function __construct(
        SerializerAdapterInterface $serializer,
        UserRepositoryInterface $userRepository,
        ValidatorAdapter $validator,
        string $importDir
    ) {
        $this->serializer = $serializer;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
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
                $authenticationTokenImport->addError('validators.authentication_token.csv.email_already_imported');

                continue;
            }

            /** @var ConstraintViolationList $emailValidations */
            $emailValidations = $this->validator->validate($authenticationTokenImportView->email, ValidatorInterface::VALIDATOR_EMAIL_TYPE);
            if ($emailValidations->count() > 0) {
                $authenticationTokenImport->addError('validators.authentication_token.csv.email.error');

                continue;
            }

            if (false === $this->userRepository->emailExists($authenticationTokenImportView->email)) {
                $authenticationTokenImport->addError('validators.authentication_token.csv.unknown_email');

                continue;
            }

            $importedEmail[$authenticationTokenImportView->email] = true;
        }

        return $authenticationTokenImports;
    }
}
