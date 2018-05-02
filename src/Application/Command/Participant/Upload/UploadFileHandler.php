<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant\Upload;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    public function __construct(FileStorageInterface $fileStorage)
    {
        $this->fileStorage = $fileStorage;
    }

    /**
     * @return array of registration or sheet data
     * @throws UploadFileException
     */
    public function handle(UploadFile $uploadFile): array
    {
        $object = $uploadFile->getObject();

        if (!$object->getFile() instanceof UploadedFile) {
            return $uploadFile->getData();
        }

        try {
            if ($object->getContentValue()) {
                // Remove previous file
                $this->fileStorage->remove($object->getContentValue());
            }

            $path = $this->fileStorage->upload($object->getFile());
            $extension = $object->getFile()->getClientOriginalExtension();

            $objectData = [
                'path'=> $path,
                'extension' => $extension,
            ];

            if ($object instanceof Image) {
                $objectData = [
                    'image' => $path,
                ];
            }

            return array_merge(
                $uploadFile->getData(),
                [
                    $object->getKey() => $objectData,
                ]
            );
        } catch (\Exception $exception) {
            throw new UploadFileException(
                sprintf(
                    'account.profile.%s.error',
                    $object instanceof UploadObject ? 'uploadedObject' : 'updateAvatar'
                ),
                $exception->getCode(),
                $exception
            );
        }
    }
}
