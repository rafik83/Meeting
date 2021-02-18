<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrintController extends AbstractController
{
    private FileSystemAdapterInterface $fileSystem;

    public function __construct(FileSystemAdapterInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function generateAction(Event $event, string $hash, File $file): BinaryFileResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($file->getHash() !== $hash) {
            throw $this->createNotFoundException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        if (!$this->fileSystem->exists($file->getPath())) {
            throw $this->createNotFoundException(sprintf('File %s not found', $file->getId()));
        }

        return new BinaryFileResponse($file->getPath());
    }
}
