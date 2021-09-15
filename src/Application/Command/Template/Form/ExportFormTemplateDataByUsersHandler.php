<?php

namespace Proximum\Vimeet\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateData\MailToAdmin;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateData\MailToAdminHandler;
use Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData\FormTemplateDataUserListViewQuery;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\File;

class ExportFormTemplateDataByUsersHandler
{
    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var string */
    private $exportFormTemplateDataPath;

    /** @var FileFactory */
    private $fileFactory;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var MailToAdminHandler */
    private $mailToAdminHandler;

    public function __construct(
        SerializerAdapterInterface $serializerAdapter,
        QueryBusInterface $queryBus,
        FileStorageInterface $fileStorage,
        string $exportFormTemplateDataPath,
        FileFactory $fileFactory,
        \DateTimeInterface $dateTime,
        MailToAdminHandler $mailToAdminHandler
    ) {
        $this->serializerAdapter = $serializerAdapter;
        $this->queryBus = $queryBus;
        $this->fileStorage = $fileStorage;
        $this->exportFormTemplateDataPath = $exportFormTemplateDataPath;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->mailToAdminHandler = $mailToAdminHandler;
    }

    public function handle(ExportFormTemplateDataByUsers $command): void
    {
        $userListView = $this->queryBus->handle(
            new FormTemplateDataUserListViewQuery(
                $command->event,
                $command->formTemplate,
                $command->users,
                $command->locale
            )
        );

        $csvContent = $this->serializerAdapter->serialize(
            $userListView,
            'csv',
            ['csv_delimiter' => ';',]
        );

        $filePath = $this->fileStorage->create(
            substr($csvContent, strpos($csvContent, "\n") + 1), // Remove first line of the file that contains the keys
            sprintf(
                'export_form_template_%d_%d_%s.csv',
                $command->event->getId(),
                $command->formTemplate->getId(),
                $this->dateTime->format('H_i_s_d_m_Y')
            ),
            $this->exportFormTemplateDataPath
        );
        $file = $this->fileFactory->createAndPersistFile($filePath, File::TYPE_EXPORT_FORM_TEMPLATE_DATA);

        $this->mailToAdminHandler->handle(
            new MailToAdmin($command->admin, $file, $command->event, $command->locale)
        );
    }
}
