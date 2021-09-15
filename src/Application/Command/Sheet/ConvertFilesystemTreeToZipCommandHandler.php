<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FinderAdapterInterface;
use Proximum\Vimeet\Application\Adapter\ZipArchiveAdapterInterface;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class ConvertFilesystemTreeToZipCommandHandler
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    /** @var FinderAdapterInterface */
    private $finderAdapter;

    /** @var ZipArchiveAdapterInterface */
    private $zipArchiveAdapter;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileSystemAdapterInterface $fileSystemAdapter,
        FinderAdapterInterface $finderAdapter,
        ZipArchiveAdapterInterface $zipArchiveAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->fileRepository = $fileRepository;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->finderAdapter = $finderAdapter;
        $this->zipArchiveAdapter = $zipArchiveAdapter;
        $this->dateTime = $dateTime;
    }

    public function handle(ConvertFilesystemTreeToZipCommand $command): ?File
    {
        $files = $this->finderAdapter->filesIn($command->rootDir);

        if (0 === \count($files)) {
            return null;
        }

        $path = explode('/', $command->rootDir);
        $rootFolder = end($path);
        $file = new File($rootFolder . '.zip', $this->dateTime, File::TYPE_UPLOADED_OBJECTS_ZIP);

        $this->zipArchiveAdapter->zipFiles($files, $command->rootDir . '.zip', $command->rootDir, $command->password);
        $this->fileRepository->add($file);
        $this->fileSystemAdapter->remove($command->rootDir);

        return $file;
    }
}
