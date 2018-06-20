<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Sheet\GetUploadedObjectsTreeQuery;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Sheet\PasswordGenerator;

class ExportUploadedObjectsCommandHandler
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var MailerInterface */
    private $mailer;

    public function __construct(
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        MailerInterface $mailer
    ) {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->mailer = $mailer;
    }

    public function handle(ExportUploadedObjectsCommand $command): void
    {
        /** @var UploadedObjectsTreeView $uploadedObjectsTreeView */
        $uploadedObjectsTreeView = $this->queryBus->handle(new GetUploadedObjectsTreeQuery($command->sheets, $command->admin));

        if (0 === \count($uploadedObjectsTreeView->tree)) {
            return;
        }

        $password = PasswordGenerator::generate();

        /** @var string $rootDir */
        $rootDir = $this->commandBus->handle(new SaveTreeToFileSystemCommand($uploadedObjectsTreeView));

        /** @var File $file */
        $file = $this->commandBus->handle(new ConvertFilesystemTreeToZipCommand($rootDir, $password));

        if (!$file instanceof File) {
            return;
        }

        // todo : send password
        //$this->mailer->send();

        // todo : send file
        //$this->mailer->send();
    }
}
