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
use Proximum\Vimeet\Application\Adapter\SheetDecryptFileInterface;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;

class SaveTreeToFileSystemCommandHandler
{
    /** @var UserEventDecryptFileInterface */
    private $userEventDecryptFile;

    /** @var SheetDecryptFileInterface */
    private $sheetDecryptFile;

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
        SheetDecryptFileInterface $sheetDecryptFile,
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
        $this->sheetDecryptFile = $sheetDecryptFile;
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
        $originalPath = $this->encryptedFilesPath . $uploadedObjectView->path;

        if (!$this->fileSystemAdapter->exists($originalPath)) {
            return;
        }

        if ($uploadedObjectView->isSheetData) {
            $this->sheetDecryptFile->decryptFile(
                $uploadedObjectView->sheet,
                $originalPath,
                $destinationPath
            );
        } else {
            $this->userEventDecryptFile->decryptFile(
                $uploadedObjectView->sheet->getEvent(),
                $uploadedObjectView->user,
                $originalPath,
                $destinationPath
            );
        }
    }
}
