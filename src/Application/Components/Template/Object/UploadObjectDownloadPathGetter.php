<?php

namespace Proximum\Vimeet\Application\Components\Template\Object;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Command\Encryption\Decrypt;
use Proximum\Vimeet\Application\Command\Encryption\DecryptHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\RemoveDecryptedFileEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\Exception\FileNotFoundException;
use Proximum\Vimeet\Domain\Template\Exception\NotUploadObjectException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject\UploadObject;

class UploadObjectDownloadPathGetter
{
    /** @var DecryptHandler */
    private $decryptHandler;

    /** @var string */
    private $webDir;

    /** @var string */
    private $encryptedFilesPath;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    public function __construct(
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        DecryptHandler $decryptHandler,
        FileSystemAdapterInterface $fileSystemAdapter,
        string $webDir,
        string $encryptedFilesPath
    ) {
        $this->decryptHandler = $decryptHandler;
        $this->webDir = $webDir;
        $this->encryptedFilesPath = $encryptedFilesPath;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->fileSystemAdapter = $fileSystemAdapter;
    }

    /**
     * @param TemplateData     $templateData
     * @param string           $objectKey
     * @param Sheet            $sheet
     * @param null|Participant $participant
     *
     * @return string
     *
     * @throws NotUploadObjectException
     */
    public function getDownloadPath(
        TemplateData $templateData,
        string $objectKey,
        Sheet $sheet,
        ?Participant $participant
    ): string {
        $uploadObject = $templateData->getObject($objectKey);

        if (!$uploadObject instanceof UploadObject || null === $uploadObject->getPath()) {
            throw new NotUploadObjectException('Invalid object');
        }

        $downloadPath = $this->webDir . $uploadObject->getPath();

        if ($uploadObject->isCrypted()) {
            $user = $participant instanceof Participant ? $participant->getUser() : $sheet->getOwner();
            $directoryStructure = explode('/', $uploadObject->getPath());
            $filename = sprintf('decrypted_%s', end($directoryStructure));
            $downloadPath = $this->encryptedFilesPath . $filename;

            if (!$this->fileSystemAdapter->exists($this->encryptedFilesPath . $uploadObject->getPath())) {
                throw new FileNotFoundException();
            }

            $this->decryptHandler->handle(
                new Decrypt(
                    $sheet,
                    $user,
                    $uploadObject->hasTag(Tag::SHEET_DATA),
                    $this->encryptedFilesPath . $uploadObject->getPath(),
                    $downloadPath
                )
            );

            $this->delayedEventDispatcher->dispatch(
                Events::REMOVE_DECRYPTED_FILE,
                new RemoveDecryptedFileEvent($downloadPath)
            );
        }

        if (!$this->fileSystemAdapter->exists($downloadPath)) {
            throw new FileNotFoundException();
        }

        return $downloadPath;
    }
}
