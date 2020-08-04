<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Adapter\UuidGeneratorInterface;
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

    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    public function __construct(
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        ZipRecordArchiveStorageInterface $zipRecordArchiveStorage,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->zipRecordArchiveStorage = $zipRecordArchiveStorage;
        $this->zipRecordArchiveStorage = $zipRecordArchiveStorage;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function handle(DownloadWebinarQuery $query): ?FileTemporary
    {
        if (!$query->happening->isWebinarRecorded()) {
            throw new \RuntimeException('This webinar has not the recorded option');
        }

        /** @var RecordArchive[] */
        $recordedArchives = $this->recordArchiveRepository->getRecordArchivesForHappening($query->happening);

        if (count($recordedArchives) === 1) {
            return $recordedArchives[0]->getPath();
        }

        $fileName = sprintf('webinar-%d.zip', $query->happening->getId());
        $remotePath = sprintf('multiple-archives/%s', $fileName);

        if ($query->regenerate) {
            $this->zipRecordArchiveStorage->delete($remotePath);

            return null;
        }

        $fileTemporary = new FileTemporary(sprintf('%s/%s', sys_get_temp_dir(), $this->uuidGenerator->generate()), $fileName);

        if (!$this->zipRecordArchiveStorage->download($remotePath, $fileTemporary->getTempFilePath())) {
            return null;
        }

        return $fileTemporary;
    }
}
