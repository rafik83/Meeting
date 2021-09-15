<?php

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

class ImportJobCreatorHandler
{
    /** @var LocalFileStorageAdapter */
    private $fileStorageAdapter;

    /** @var string */
    private $importLocationDirectoryPath;

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var JobQueueInterface */
    private $jobQueue;

    /**
     * ImportJobCreatorHandler constructor.
     *
     * @param LocalFileStorageAdapter $fileStorageAdapter
     * @param string                  $importLocationDirectoryPath
     * @param FileRepositoryInterface $fileRepository
     * @param \DateTimeInterface      $dateTime
     * @param JobQueueInterface       $jobQueue
     */
    public function __construct(
        LocalFileStorageAdapter $fileStorageAdapter,
        $importLocationDirectoryPath,
        FileRepositoryInterface $fileRepository,
        \DateTimeInterface $dateTime,
        JobQueueInterface $jobQueue
    ) {
        $this->fileStorageAdapter          = $fileStorageAdapter;
        $this->importLocationDirectoryPath = $importLocationDirectoryPath;
        $this->fileRepository              = $fileRepository;
        $this->dateTime                    = $dateTime;
        $this->jobQueue                    = $jobQueue;
    }

    /**
     * @param ImportJobCreator $command
     */
    public function handle(ImportJobCreator $command)
    {
        $content = file_get_contents($command->file);

        $file = $this->createFile($command->event, $content);

        $this->jobQueue->importPlannerForEvent(
            $file,
            $command->event,
            $command->admin,
            $command->admin->getLocale()
        );
    }

    /**
     * @param Event  $event
     * @param string $data
     *
     * @return File
     */
    private function createFile(Event $event, &$data)
    {
        $filePath = $this->fileStorageAdapter->create(
            $data,
            sprintf('import_planner_%s.xml', $event->getId()),
            $this->importLocationDirectoryPath
        );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
