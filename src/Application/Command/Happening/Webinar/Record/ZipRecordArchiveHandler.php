<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use OpenTok\Archive;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Psr\Log\LoggerInterface;

class ZipRecordArchiveHandler
{
    /** @var ZipRecordArchiveStorageInterface */
    public $zipRecordArchiveStorage;

    /** @var FileSystemAdapterInterface */
    private $fileSystem;

    /** @var VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        ZipRecordArchiveStorageInterface $zipRecordArchiveStorage,
        FileSystemAdapterInterface $fileSystem,
        VideoConferenceAdapterInterface $videoConferenceAdapter,
        HappeningRepositoryInterface $happeningRepository,
        LoggerInterface $logger
    ) {
        $this->zipRecordArchiveStorage = $zipRecordArchiveStorage;
        $this->fileSystem = $fileSystem;
        $this->videoConferenceAdapter = $videoConferenceAdapter;
        $this->happeningRepository = $happeningRepository;
        $this->logger = $logger;
    }

    public function handle(ZipRecordArchive $command): void
    {
        $happening = $command->happening;

        if (!$happening->isWebinarRecorded()) {
            throw new \RuntimeException('This webinar has not the recorded option');
        }

        if (empty($happening->getWebinarSessionId())) {
            throw new \RuntimeException('This webinar has no session id');
        }

        if ($command->regenerate && $happening->hasWebinarRecordZipFileUrl()) {
            $this->zipRecordArchiveStorage->delete($happening->getWebinarRecordZipFileUrl());
            $happening->addWebinarRecordZipFileUrl(null);
        }

        if ($happening->hasWebinarRecordZipFileUrl()) {
            return;
        }

        $archiveList = $this->videoConferenceAdapter->listArchives($happening->getWebinarSessionId());

        $files = [];
        $index = 1;

        /** @var Archive $archive */
        foreach ($archiveList->getItems() as $archive) {
            $archiveFileName = sprintf('webinar-%d-part%d.mp4', $happening->getId(), $index);
            $files[$archiveFileName] = $archive->url;

            ++$index;
        }

        if (empty($files)) {
            throw new \RuntimeException('This webinar has no recorded file');
        }

        $fileName = sprintf('webinar-%d.zip', $happening->getId());
        $remotePath = sprintf('multiple-archives/%s', $fileName);

        $zipFileName = sprintf(
            '%s/%s',
            $this->fileSystem->getTemporaryPath(),
            $fileName
        );

        $this->zipRecordArchiveStorage->prepareZip(
            $files,
            $zipFileName
        );

        $remoteUrl = $this->zipRecordArchiveStorage->upload(
            $zipFileName,
            $happening->getEvent(),
            $remotePath
        );

        $this->logger->notice(
            sprintf('VIMEET : Zip record archive of happening webinar %d is uploaded on %s', $happening->getId(), $remoteUrl)
        );

        $this->fileSystem->remove($zipFileName);

        $happening->addWebinarRecordZipFileUrl($remoteUrl);
        $this->happeningRepository->set($happening);
    }
}
