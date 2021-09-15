<?php

namespace Proximum\Vimeet\Application\Command\Spot\Import;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Repository\FileRepositoryInterface;

class SpotImportHandler
{
    /** @var FileRepositoryInterface */
    private $fileRepository;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var string */
    private $importDir;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param FileRepositoryInterface $fileRepository
     * @param FileStorageInterface    $fileStorage
     * @param string                  $importDir
     * @param \DateTimeInterface      $dateTime
     */
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

    /**
     * @param SpotImport $spotImport
     *
     * @return File
     */
    public function handle(SpotImport $spotImport): File
    {
        $fileContent = Charset::convertString(file_get_contents($spotImport->file), $spotImport->charset, Charset::UTF_8);

        $filePath = $this
            ->fileStorage
            ->create(
                $fileContent,
                basename($spotImport->file),
                $this->importDir
            );

        $file = new File($filePath, $this->dateTime);
        $this->fileRepository->add($file);

        return $file;
    }
}
