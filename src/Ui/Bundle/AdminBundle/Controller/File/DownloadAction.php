<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\File;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var string */
    private $path;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FileSystemAdapterInterface $fileSystemAdapter,
        string $path
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->path = $path;
    }

    public function __invoke(Event $event, string $hash, File $file): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
        ) {
            throw new AccessDeniedException('Access Denied for this page');
        }

        if ($file->getHash() !== $hash) {
            throw new NotFoundHttpException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        $path = sprintf('%s%s', $this->path, $file->getPath());

        if (!$this->fileSystemAdapter->exists($path)) {
            throw new NotFoundHttpException(sprintf('File %s not found', $file->getId()));
        }

        if ('csv' !== pathinfo($path, PATHINFO_EXTENSION)) {
            return new CsvFileResponse(file_get_contents($path), basename($path));
        }

        $response = new BinaryFileResponse($path);
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($path));
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
