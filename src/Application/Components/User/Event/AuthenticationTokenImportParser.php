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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\AuthenticationTokenImport;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\ValidatorAdapter;

class AuthenticationTokenImportParser
{
    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ValidatorAdapter */
    private $validator;

    /** @var TranslatorAdapter */
    private $translator;

    /** @var string */
    private $importDir;

    public function __construct(
        SerializerAdapterInterface $serializer,
        SheetRepositoryInterface $sheetRepository,
        ValidatorAdapter $validator,
        TranslatorAdapter $translator,
        string $importDir
    ) {
        $this->serializer = $serializer;
        $this->sheetRepository = $sheetRepository;
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

        /** @var AuthenticationTokenImport $authenticationTokenImport */
        foreach ($authenticationTokenImports as $authenticationTokenImport) {
            if (null === $authenticationTokenImport->authenticationTokenImportView) {
                continue;
            }
        }

        return $authenticationTokenImports;
    }
}
