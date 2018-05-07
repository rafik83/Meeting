<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\User\Event\Denormalizer;

use Proximum\Vimeet\Application\View\User\Event\AuthenticationTokenImportView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthenticationTokenDenormalizer implements DenormalizerInterface
{
    private const KEY_TOKEN = 'token';
    private const KEY_EMAIL = 'email';
    private const KEY_EXPIRATION = 'expiration';

    private const ALLOWED_KEYS = [
        self::KEY_EMAIL,
        self::KEY_TOKEN,
        self::KEY_EXPIRATION,
    ];

    /** @var TranslatorAdapter */
    private $translator;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(TranslatorAdapter $translator, ValidatorInterface $validator)
    {
        $this->translator = $translator;
        $this->validator = $validator;
    }

    public function denormalize($data, $class, $format = null, array $context = array()): iterable
    {
        if (!$context['event'] instanceof Event) {
            throw new \InvalidArgumentException();
        }

        $imports = [];

        foreach ($data as $key => $row) {
            try {
                if (false === $this->areKeysValid($row)) {
                    throw new \Exception(
                        $this->translator->trans('validators.authentication_token.csv.invalid_keys', [], 'validators')
                    );
                }

                if ($row[self::KEY_EXPIRATION]) {
                    $dateValidations = $this->validator->validate($row[self::KEY_EXPIRATION], [new Date()]);
                    if ($dateValidations->count() > 0) {
                        throw new \Exception(
                            $this->translator->trans('validators.authentication_token.csv.invalid_expiration_date', [], 'validators')
                        );
                    }
                }

                $authenticationTokenImport = new AuthenticationTokenImport(
                    new AuthenticationTokenImportView(
                        $context['event'],
                        $row[self::KEY_EMAIL],
                        $row[self::KEY_TOKEN],
                        $row[self::KEY_EXPIRATION] ? new \DateTime($row[self::KEY_EXPIRATION]) : null
                    )
                );
            } catch (\Exception $exception) {
                $authenticationTokenImport = (new AuthenticationTokenImport())
                    ->addError($exception->getMessage());
            }

            $imports[] = $authenticationTokenImport;
        }

        return $imports;
    }

    private function areKeysValid(array &$row): bool
    {
        foreach (self::ALLOWED_KEYS as $key) {
            if (!array_key_exists($key, $row)) {
                return false;
            }
        }

        return true;
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return 'csv' === $format && AuthenticationTokenImport::class === $type;
    }
}
