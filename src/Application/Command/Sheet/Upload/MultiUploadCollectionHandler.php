<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Upload;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Nomenclature\Id\UniquIdGenerator;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultiUploadCollectionHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var UniquIdGenerator */
    private $uniquIdGenerator;

    /** @var string */
    private $sharedUploadedFilesPath;

    public function __construct(
        FileStorageInterface $fileStorage,
        UniquIdGenerator $uniquIdGenerator,
        string $sharedUploadedFilesPath
    ) {
        $this->fileStorage = $fileStorage;
        $this->sharedUploadedFilesPath = $sharedUploadedFilesPath;
        $this->uniquIdGenerator = $uniquIdGenerator;
    }

    public function handle(MultiUploadCollection $command): array
    {
        $data = [];
        $savedUploadsIndexedByUniqId = $command->savedMultiUploadCollectionObject->getUploadsIndexedByUniqid();

        /** @var MultiUploadObject $uploadObject */
        foreach ($command->initialMultiUploadCollectionObject->getUploads() as $uploadObject) {
            if (!array_key_exists($uploadObject->getUniqId(), $savedUploadsIndexedByUniqId)) {
                $this->fileStorage->remove($this->sharedUploadedFilesPath.$uploadObject->getPath());
            }
        }

        /** @var MultiUploadObject $uploadObject */
        foreach ($command->savedMultiUploadCollectionObject->getUploads() as $uploadObject) {
            $file = $uploadObject->getFile();

            if (!$file instanceof UploadedFile) {
                $data[] = $uploadObject->getDefaultValues();

                continue;
            }

            if ($uploadObject->getPath()) {
                $this->fileStorage->remove($this->sharedUploadedFilesPath . $uploadObject->getPath());
            }
            $path = $this->fileStorage->upload($file, $this->sharedUploadedFilesPath);

            $data[] = [
                'path' => $path,
                'title' => $uploadObject->getTitle(),
                'uniqId' => $uploadObject->getUniqId() ?? $this->uniquIdGenerator->generate(),
            ];
        }

        return $data;
    }
}
