<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveExpiredFilesCommand extends Command
{
    public const NAME = 'vimeet:remove:expired_files';

    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileSystemAdapterInterface $fileSystemAdapter,
        \DateTimeInterface $dateTime,
        string $sharedUploadedFiles
    ) {
        parent::__construct(self::NAME);

        $this->fileRepository = $fileRepository;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->dateTime = $dateTime;
        $this->sharedUploadedFiles = $sharedUploadedFiles;
    }

    public function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Remove expired uploaded objects ZIP files');
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $files = $this->fileRepository->findExpiredFiles($this->dateTime);

        /** @var File $file */
        foreach ($files as $file) {
            if (File::TYPE_UPLOADED_OBJECTS_ZIP === $file->getType()) {
                $this->removeUploadedObject($file);
            }
        }
    }

    private function removeUploadedObject(File $file): void
    {
        $filePath = sprintf('%s/%s', $this->sharedUploadedFiles, $file->getPath());

        if (!$this->fileSystemAdapter->exists($filePath)) {
            return;
        }

        $this->fileSystemAdapter->remove($filePath);
        $this->fileRepository->remove($file);
    }
}
