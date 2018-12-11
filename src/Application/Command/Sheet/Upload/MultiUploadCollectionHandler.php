<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Upload;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MultiUploadCollectionHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        FileStorageInterface $fileStorage,
        string $sharedUploadedFiles
    ) {
        $this->fileStorage = $fileStorage;
        $this->sharedUploadedFiles = $sharedUploadedFiles;
    }

    public function handle(MultiUploadCollection $command): array
    {
        $data = [];
        $savedUploadsIndexedByUniqId = $command->savedMultiUploadCollectionObject->getUploadsIndexedByUniqid();

        /** @var MultiUploadObject $uploadObject */
        foreach ($command->initialMultiUploadCollectionObject->getUploads() as $uploadObject) {
            if (!array_key_exists($uploadObject->getUniqId(), $savedUploadsIndexedByUniqId)) {
                $this->fileStorage->remove($this->sharedUploadedFiles.$uploadObject->getPath());
            }
        }

        /** @var MultiUploadObject $uploadObject */
        foreach ($command->savedMultiUploadCollectionObject->getUploads() as $uploadObject) {
            $file = $uploadObject->getFile();

            if (!$file instanceof UploadedFile) {
                $data[] = $uploadObject->getDefaultValues();

                continue;
            }

            $this->fileStorage->remove($this->sharedUploadedFiles.$uploadObject->getPath());
            $path = $this->fileStorage->upload($file, $this->sharedUploadedFiles);

            $data[] = [
                'path' => $path,
                'title' => $uploadObject->getTitle(),
                'uniqId' => $uploadObject->getUniqId() ?? uniqid(),
            ];
        }

        return $data;
    }
}
