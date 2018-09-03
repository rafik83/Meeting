<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;
use Proximum\Vimeet\Domain\Model\User;

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
                    $this->handleCryptedFile($uploadedObject, $destinationPath);

                    continue;
                }

                $originalPath = $this->webDir . $uploadedObject->path;
                if (!$this->fileSystemAdapter->exists($originalPath)) {
                    continue;
                }

                $this->fileSystemAdapter->copy($originalPath, $destinationPath);
            }
        }

        return $rootDir;
    }

    private function handleCryptedFile(UploadedObjectView $uploadedObjectView, string $destinationPath): void
    {
        $owner = $uploadedObjectView->user instanceof User ? $uploadedObjectView->user : $uploadedObjectView->sheet->getOwner();
        $originalPath = $this->encryptedFilesPath . $uploadedObjectView->path;

        if (!$this->fileSystemAdapter->exists($originalPath)) {
            return;
        }

        $this->userEventDecryptFile->decryptFile(
            $uploadedObjectView->sheet->getEvent(),
            $owner,
            $originalPath,
            $destinationPath
        );
    }
}
