<?php

namespace Proximum\Vimeet\Application\Command\Event;

class ArchiveUnArchiveHandler
{
    /** @var ArchiveHandler */
    private $archiveHandler;

    /** @var UnArchiveHandler */
    private $unArchiveHandler;

    /**
     * @param ArchiveHandler   $archiveHandler
     * @param UnArchiveHandler $unArchiveHandler
     */
    public function __construct(ArchiveHandler $archiveHandler, UnArchiveHandler $unArchiveHandler)
    {
        $this->archiveHandler = $archiveHandler;
        $this->unArchiveHandler = $unArchiveHandler;
    }

    /**
     * @param ArchiveUnArchive $command
     *
     * @return null|string
     */
    public function handle(ArchiveUnArchive $command): ?string
    {
        if ($command->archive) {
            $this->archiveHandler->handle(new Archive($command->event));

            return ArchiveUnArchive::ARCHIVED;
        }

        if ($command->unArchive) {
            $this->unArchiveHandler->handle(new UnArchive($command->event));

            return ArchiveUnArchive::UN_ARCHIVED;
        }

        return null;
    }
}
