<?php

namespace Proximum\Vimeet\Application\Query\Happening\Admin;

use Proximum\Vimeet\Application\Adapter\ZipRecordArchiveStorageInterface;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;

class WebinarDownloadQueryHandler
{
    /** @var RecordArchiveRepositoryInterface */
    public $recordArchiveRepository;

    /** @var ZipRecordArchiveStorageInterface */
    public $zipRecordArchiveStorage;

    public function __construct(
        RecordArchiveRepositoryInterface $recordArchiveRepository,
        ZipRecordArchiveStorageInterface $zipRecordArchiveStorage
    ) {
        $this->recordArchiveRepository = $recordArchiveRepository;
        $this->zipRecordArchiveStorage = $zipRecordArchiveStorage;
    }

    public function handle(WebinarDownloadQuery $query): ?string
    {
        if (!$query->happening->isWebinarRecorded()) {
            throw new \RuntimeException('This webinar has not the recorded option');
        }

        /** @var RecordArchive[] */
        $recordedArchives = $this->recordArchiveRepository->getRecordArchivesForHappening($query->happening);

        if (count($recordedArchives) === 1) {
            return $recordedArchives[0]->getPath();
        }

        // $tempFilePath = sprintf('%s/webinar-%d.zip', sys_get_temp_dir(), $query->happening->getId());
        $url = $this->zipRecordArchiveStorage->getUrl(sprintf('multiple-archives/webinar-%d.zip', $query->happening->getId());

        return $url;
    }
}
