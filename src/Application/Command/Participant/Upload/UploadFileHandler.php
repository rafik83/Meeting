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
use Proximum\Vimeet\Application\Adapter\UserEventEncryptFileInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileHandler
{
    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var UserEventEncryptFileInterface */
    private $userEventEncryptFile;

    /** @var string */
    private $encryptedFilesPath;

    public function __construct(
        FileStorageInterface $fileStorage,
        UserEventEncryptFileInterface $userEventEncryptFile,
        string $encryptedFilesPath
    ) {
        $this->fileStorage = $fileStorage;
        $this->userEventEncryptFile = $userEventEncryptFile;
        $this->encryptedFilesPath = $encryptedFilesPath;
    }

    /**
     * @return array of registration or sheet data
     * @throws UploadFileException
     */
    public function handle(UploadFile $uploadFile): array
    {
        $object = $uploadFile->getObject();
        $file = $object->getFile();

        if (!$file instanceof UploadedFile) {
            return $uploadFile->getData();
        }

        try {
            $isEncrypted = $object instanceof UploadObject && $object->isCrypted();

            if ($object->getContentValue()) {
                // Remove previous file
                $this->fileStorage->remove(
                    ($isEncrypted ? $this->encryptedFilesPath : '') . $object->getContentValue(),
                    $isEncrypted
                );
            }

            $clientOriginalExtension = $file->getClientOriginalExtension();
            $path = $this->fileStorage->upload($file, $isEncrypted ? $this->encryptedFilesPath : null);

            if ($isEncrypted) {
                $this->encryptFile($uploadFile->getEvent(), $uploadFile->getUser(), $path);
            }

            $objectData = [
                'path'=> $path,
                'extension' => $clientOriginalExtension,
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

    private function encryptFile(Event $event, User $user, string $path): void
    {
        $initialFilename = $this->encryptedFilesPath . $path;
        $encryptedFilename = $initialFilename . '_encrypted';

        $this->userEventEncryptFile->encryptFile(
            $event,
            $user,
            $initialFilename,
            $encryptedFilename
        );

        $this->fileStorage->remove($initialFilename, true);
        $this->fileStorage->rename($encryptedFilename, $initialFilename, true);
    }
}
