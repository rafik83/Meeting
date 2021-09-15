<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Spot;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var string */
    private $exportPath;

    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FileSystemAdapterInterface $fileSystemAdapter,
        string $exportPath
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->exportPath = $exportPath;
        $this->fileSystemAdapter = $fileSystemAdapter;
    }

    /**
     * @param Event  $event
     * @param string $hash
     * @param File   $file
     *
     * @return CsvFileResponse
     */
    public function __invoke(Event $event, $hash, File $file): CsvFileResponse
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

        $path = sprintf('%s%s', $this->exportPath, $file->getPath());

        if (!$this->fileSystemAdapter->exists($path)) {
            throw new NotFoundHttpException(sprintf('File %s not found', $file->getId()));
        }

        return new CsvFileResponse(
            file_get_contents($path),
            basename($path)
        );
    }
}
