<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Command\Encryption\Decrypt;
use Proximum\Vimeet\Application\Command\Encryption\DecryptHandler;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;

class SaveTreeToFileSystemCommandHandler
{
    /** @var DecryptHandler */
    private $decryptHandler;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    /** @var string */
    private $webDir;

    /** @var string */
    private $encryptedFilesPath;

    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        DecryptHandler $decryptHandler,
        FileSystemAdapterInterface $fileSystemAdapter,
        string $sharedUploadedFiles,
        string $encryptedFilesPath,
        string $webDir
    ) {
        $this->decryptHandler = $decryptHandler;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->sharedUploadedFiles = $sharedUploadedFiles;
        $this->encryptedFilesPath = $encryptedFilesPath;
        $this->webDir = $webDir;
    }

    public function handle(SaveTreeToFileSystemCommand $command): string
    {
        $rootDir = $this->sharedUploadedFiles . '/' . uniqid();

        foreach ($command->uploadedObjectsTreeView->tree as $uploadedObjectNodeView) {
            $nodeDir = $rootDir . '/' . substr($uploadedObjectNodeView->folder, 0, 255);
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

        $this->decryptHandler->handle(
            new Decrypt(
                $uploadedObjectView->sheet,
                $uploadedObjectView->user,
                $uploadedObjectView->isSheetData,
                $originalPath,
                $destinationPath
            )
        );
    }
}
