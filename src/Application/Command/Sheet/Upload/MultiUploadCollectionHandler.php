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

        /** @var MultiUploadObject $uploadObject */
        foreach ($command->multiUploadObjects as $key => $uploadObject) {
            $file = $uploadObject->getFile();

            if (!$file instanceof UploadedFile) {
                $data[$key] = [
                    'path' => $uploadObject->getPath(),
                    'title' => $uploadObject->getTitle(),
                ];

                continue;
            }

            if ($uploadObject->getPath()) {
                // Remove previous file
                $this->fileStorage->remove($this->sharedUploadedFiles.$uploadObject->getPath());
            }

            $path = $this->fileStorage->upload($file, $this->sharedUploadedFiles);

            $data[$key] = [
                'path' => $path,
                'title' => $uploadObject->getTitle(),
            ];
        }

        return $data;
    }
}
