<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FinderAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipArchiveAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Domain\File\FileTemporary;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class AggregateHandler
{
    /** @var RecordArchiveRepositoryInterface */
    private $recordArchiveRepository;

    /** @var ZipArchiveAdapterInterface */
    private $zipArchiveAdapter;

    /** @var ZipRecordArchiveStorageInterface */
    private $zipRecordArchiveStorage;

    /** @var FinderAdapterInterface */
    private $finderAdapter;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    public function __construct(
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        ZipArchiveAdapterInterface $zipArchiveAdapter,
        ZipRecordArchiveStorageInterface $zipRecordArchiveStorage,
        FinderAdapterInterface $finderAdapter,
        FileSystemAdapterInterface $fileSystemAdapter
    ) {
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->zipArchiveAdapter = $zipArchiveAdapter;
        $this->zipRecordArchiveStorage = $zipRecordArchiveStorage;
        $this->finderAdapter = $finderAdapter;
        $this->fileSystemAdapter = $fileSystemAdapter;
    }

    public function handle(Aggregate $command): ?FileTemporary
    {
        $recordArchives = $this->recordArchiveRepository->getRecordArchivesForHappening($command->happening);

        if (count($recordArchives) < 2) {
            return null;
        }

        $files = [];
        $index = 1;

        $tempDir = $this->fileSystemAdapter->createTempDir();

        foreach ($recordArchives as $recordArchive) {
            $tempFilePath = sprintf('%s/webinar-%d-part%d.mp4', $tempDir, $command->happening->getId(), $index);
            if ($recordArchive->getPath()) {
                $this->fileSystemAdapter->copy($recordArchive->getPath(), $tempFilePath);
                $index++;
            }
        }
        $files = $this->finderAdapter->filesIn($tempDir);

        if (count($files) === 0) {
            throw new \RuntimeException('No archive found for webinar');
        }

        $fileName = sprintf('webinar-%d.zip', $command->happening->getId());
        $zipFilePath = $this->fileSystemAdapter->getTemporaryPath();
        $this->zipArchiveAdapter->zipFiles($files, $zipFilePath, '');

        $this->zipRecordArchiveStorage->upload($zipFilePath, 'multiple-archives/' . $fileName);

        $this->fileSystemAdapter->remove($tempDir);

        return new FileTemporary($zipFilePath, $fileName);
    }
}
