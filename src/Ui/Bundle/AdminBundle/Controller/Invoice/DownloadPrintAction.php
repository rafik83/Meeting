<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Invoice;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\FileSystemAdapter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadPrintAction
{
    /** @var FileSystemAdapter */
    private $fileSystemAdapter;

    public function __construct(FileSystemAdapter $fileSystemAdapter)
    {
        $this->fileSystemAdapter = $fileSystemAdapter;
    }

    public function __invoke(Event $event, $hash, File $file)
    {
        if ($file->getHash() !== $hash) {
            throw new AccessDeniedException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        if (!$this->fileSystemAdapter->exists($file->getPath())) {
            throw new AccessDeniedException(sprintf('File %s not found', $file->getId()));
        }

        return new BinaryFileResponse(
            $file->getPath()
        );
    }
}
