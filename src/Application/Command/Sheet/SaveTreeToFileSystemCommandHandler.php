<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Nelmio\Alice\support\models\User;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;

class SaveTreeToFileSystemCommandHandler
{
    /** @var UserEventDecryptFileInterface */
    private $userEventDecryptFile;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    /** @var string */
    private $webDir;

    /** @var string */
    private $encryptedFilesPath;

    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        UserEventDecryptFileInterface $userEventDecryptFile,
        FileSystemAdapterInterface $fileSystemAdapter,
        string $sharedUploadedFiles,
        string $encryptedFilesPath,
        string $webDir
    ) {
        $this->userEventDecryptFile = $userEventDecryptFile;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->sharedUploadedFiles = $sharedUploadedFiles;
        $this->encryptedFilesPath = $encryptedFilesPath;
        $this->webDir = $webDir;
    }

    public function handle(SaveTreeToFileSystemCommand $command): string
    {
        $rootDir = $this->sharedUploadedFiles . '/' . uniqid();

        foreach ($command->uploadedObjectsTreeView->tree as $uploadedObjectNodeView) {
            $nodeDir = $rootDir . '/' . $uploadedObjectNodeView->folder;
            $this->fileSystemAdapter->mkdir($nodeDir);

            foreach ($uploadedObjectNodeView->uploadedObjectsView as $uploadedObject) {
                $destinationPath = $nodeDir . '/' . $uploadedObject->filename;

                if (true === $uploadedObject->crypted) {
                    $owner = $uploadedObject->user instanceof User ? $uploadedObject->user : $uploadedObject->sheet->getOwner();
                    $originalPath = $this->encryptedFilesPath . $uploadedObject->path;

                    $this->userEventDecryptFile->decryptFile(
                        $uploadedObject->sheet->getEvent(),
                        $owner,
                        $originalPath,
                        $destinationPath
                    );
                } else {
                    $this->fileSystemAdapter->copy(
                        $this->webDir. $uploadedObject->path,
                        $destinationPath
                    );
                }
            }
        }

        return $rootDir;
    }
}
