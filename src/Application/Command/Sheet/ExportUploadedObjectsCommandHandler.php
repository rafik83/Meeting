<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Security\PasswordGenerator;
use Proximum\Vimeet\Application\Query\Sheet\GetUploadedObjectsTreeQuery;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\ExportUploadedObjectsPasswordMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\ExportUploadedObjectsZipMail;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\NoUploadedObjectsZipMail;

class ExportUploadedObjectsCommandHandler
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $sender;

    public function __construct(
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        MailerInterface $mailer,
        string $sender
    ) {
        $this->commandBus = $commandBus;
        $this->queryBus = $queryBus;
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    public function handle(ExportUploadedObjectsCommand $command): void
    {
        /** @var UploadedObjectsTreeView $uploadedObjectsTreeView */
        $uploadedObjectsTreeView = $this->queryBus->handle(
            new GetUploadedObjectsTreeQuery(
                $command->event,
                $command->sheets,
                $command->admin
            )
        );

        if (0 === \count($uploadedObjectsTreeView->tree)) {
            $this->mailer->send(
                new NoUploadedObjectsZipMail(
                    $command->event,
                    $this->sender,
                    $command->admin->getEmail(),
                    $command->admin->getLocale()
                )
            );

            return;
        }

        $password = PasswordGenerator::generate();

        /** @var string $rootDir */
        $rootDir = $this->commandBus->handle(new SaveTreeToFileSystemCommand($uploadedObjectsTreeView));

        /** @var File $file */
        $file = $this->commandBus->handle(new ConvertFilesystemTreeToZipCommand($rootDir, $password));

        if (!$file instanceof File) {
            $this->mailer->send(
                new NoUploadedObjectsZipMail(
                    $command->event,
                    $this->sender,
                    $command->admin->getEmail(),
                    $command->admin->getLocale()
                )
            );

            return;
        }

        $this->mailer->send(
            new ExportUploadedObjectsPasswordMail(
                $command->event,
                $password,
                $this->sender,
                $command->admin->getEmail(),
                $command->admin->getLocale()
            )
        );

        $this->mailer->send(
            new ExportUploadedObjectsZipMail(
                $command->event,
                $file,
                $this->sender,
                $command->admin->getEmail(),
                $command->admin->getLocale()
            )
        );
    }
}
