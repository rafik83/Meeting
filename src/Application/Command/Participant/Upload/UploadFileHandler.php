<?php

namespace Proximum\Vimeet\Application\Command\Participant\Upload;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Encryption\Encrypt;
use Proximum\Vimeet\Application\Command\Encryption\EncryptHandler;
use Proximum\Vimeet\Domain\Template\TemplateObject\Image;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadFileHandler
{
    /** @var EncryptHandler */
    private $encryptHandler;

    /** @var FileStorageInterface */
    private $fileStorage;

    /** @var string */
    private $encryptedFilesPath;

    public function __construct(
        EncryptHandler $encryptHandler,
        FileStorageInterface $fileStorage,
        string $encryptedFilesPath
    ) {
        $this->encryptHandler = $encryptHandler;
        $this->fileStorage = $fileStorage;
        $this->encryptedFilesPath = $encryptedFilesPath;
    }

    /**
     * @throws UploadFileException
     *
     * @return array of registration or sheet data
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

            $clientOriginalExtension = $file->guessExtension();
            $path = $this->fileStorage->upload($file, $isEncrypted ? $this->encryptedFilesPath : null);

            if ($isEncrypted) {
                $this->encryptFile($uploadFile, $path);
            }

            $objectData = [
                'path'=> $path,
                'extension' => $clientOriginalExtension,
            ];

            if ($object instanceof Image) {
                $objectData = [
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

    private function encryptFile(UploadFile $uploadFile, string $path): void
    {
        $initialFilename = $this->encryptedFilesPath . $path;
        $encryptedFilename = $initialFilename . '_encrypted';

        $this->encryptHandler->handle(
            new Encrypt(
                $uploadFile->getSheet(),
                $uploadFile->getUser(),
                $uploadFile->isSheetData(),
                $initialFilename,
                $encryptedFilename
            )
        );

        $this->fileStorage->remove($initialFilename, true);
        $this->fileStorage->rename($encryptedFilename, $initialFilename, true);
    }
}
