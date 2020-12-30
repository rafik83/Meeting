<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\RoomingListViewQuery;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\File;

class ExportRoomingListHandler
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var FileFactory */
    private $fileFactory;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $exportRoomingListPath;

    /** @var MailToAdminHandler */
    private $mailToAdminHandler;

    public function __construct(
        QueryBusInterface $queryBus,
        SerializerAdapterInterface $serializerAdapter,
        FileStorageInterface $fileStorage,
        FileFactory $fileFactory,
        string $exportRoomingListPath,
        MailToAdminHandler $mailToAdminHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->queryBus = $queryBus;
        $this->serializerAdapter = $serializerAdapter;
        $this->fileStorage = $fileStorage;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->exportRoomingListPath = $exportRoomingListPath;
        $this->mailToAdminHandler = $mailToAdminHandler;
    }

    public function handle(ExportRoomingList $command): void
    {
        $roomingListView = $this->queryBus->handle(new RoomingListViewQuery($command->event, $command->locale));

        $csvContent = $this->serializerAdapter->serialize($roomingListView, 'csv', ['csv_delimiter' => ';',]);

        $csvWithoutFirstLine = substr($csvContent, strpos($csvContent, "\n") + 1); // Remove first line of the file that contains the keys

        $filePath = $this->fileStorage->create(
            $csvWithoutFirstLine ?: '',
            sprintf(
                'export_rooming_list_%d_%s.csv',
                $command->event->getId(),
                $this->dateTime->format('H_i_s_d_m_Y')
            ),
            $this->exportRoomingListPath
        );
        $file = $this->fileFactory->createAndPersistFile($filePath, File::TYPE_EXPORT_ROOMING_LIST);

        $this->mailToAdminHandler->handle(new MailToAdmin(
            $command->event,
            $command->admin,
            $file,
            $command->locale
        ));
    }
}
