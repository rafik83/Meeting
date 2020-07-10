<?php

namespace Proximum\Vimeet\Domain\Repository\Happening\Webinar;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Webinar\RecordArchive;

interface RecordArchiveRepositoryInterface
{
    public function add(RecordArchive $recordArchive): void;

    public function update(RecordArchive $recordArchive): void;

    /**
     * @param Happening $happening
     *
     * @return RecordArchive[]
     */
    public function getRecordArchivesForHappening(Happening $happening): array;

    public function getByArchiveId(string $archiveId): ?RecordArchive;

    public function hasStartedRecordArchiveForHappening(Happening $happening): bool;

    public function getStartedRecordArchiveForHappening(Happening $happening): ?RecordArchive;
}
