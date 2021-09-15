<?php

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class AuthenticationTokenImportHandler
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var string */
    private $importDir;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        FileRepositoryInterface $fileRepository,
        FileStorageInterface $fileStorage,
        string $importDir,
        \DateTimeInterface $dateTime
    ) {
        $this->fileRepository = $fileRepository;
        $this->fileStorage = $fileStorage;
        $this->importDir = $importDir;
        $this->dateTime = $dateTime;
    }

    public function handle(AuthenticationTokenImport $authenticationTokenImport): File
    {
        $fileContent = file_get_contents($authenticationTokenImport->file);

        $filePath = $this
            ->fileStorage
            ->create(
                $fileContent,
                basename($authenticationTokenImport->file),
                $this->importDir
            );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
