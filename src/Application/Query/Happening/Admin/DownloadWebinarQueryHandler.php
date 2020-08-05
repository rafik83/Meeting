<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Domain\File\FileTemporary;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class DownloadWebinarQueryHandler
{
    /** @var RecordArchiveRepositoryInterface */
    public $recordArchiveRepository;

    /** @var ZipRecordArchiveStorageInterface */
    public $zipRecordArchiveStorage;

    /** @var FileSystemAdapterInterface */
    private $fileSystem;

    public function __construct(
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        ZipRecordArchiveStorageInterface $zipRecordArchiveStorage,
        FileSystemAdapterInterface $fileSystem
    ) {
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->zipRecordArchiveStorage = $zipRecordArchiveStorage;
        $this->fileSystem = $fileSystem;
    }

    public function handle(DownloadWebinarQuery $query): ?FileTemporary
    {
        if (!$query->happening->isWebinarRecorded()) {
            throw new \RuntimeException('This webinar has not the recorded option');
        }

        /** @var RecordArchive[] */
        $recordedArchives = $this->recordArchiveRepository->getRecordArchivesForHappening($query->happening);

        if (count($recordedArchives) === 1) {
            $fileName = sprintf('webinar-%d.mp4', $query->happening->getId());
            return new FileTemporary($recordedArchives[0]->getPath(), $fileName);
        }

        $fileName = sprintf('webinar-%d.zip', $query->happening->getId());
        $remotePath = sprintf('multiple-archives/%s', $fileName);

        if ($query->regenerate) {
            $this->zipRecordArchiveStorage->delete($remotePath);

            return null;
        }

        $fileTemporary = new FileTemporary($this->fileSystem->getTemporaryPath(), $fileName);

        if (!$this->zipRecordArchiveStorage->download($remotePath, $fileTemporary->getTempFilePath())) {
            return null;
        }

        return $fileTemporary;
    }
}
