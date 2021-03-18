<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadUploadedObjectsZipAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    /** @var string */
    private $sharedUploadedFiles;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FileSystemAdapterInterface $fileSystemAdapter,
        string $sharedUploadedFiles
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->sharedUploadedFiles = $sharedUploadedFiles;
    }

    public function __invoke(Event $event, string $hash, File $file): BinaryFileResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE') ||
            !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException();
        }

        if ($file->getHash() !== $hash) {
            throw new NotFoundHttpException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        $path = sprintf('%s/%s', $this->sharedUploadedFiles, $file->getPath());

        if (!$this->fileSystemAdapter->exists($path)) {
            throw new NotFoundHttpException(sprintf('File %s not found', $file->getId()));
        }

        return (new BinaryFileResponse($path))
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $file->getPath()
            );
    }
}
