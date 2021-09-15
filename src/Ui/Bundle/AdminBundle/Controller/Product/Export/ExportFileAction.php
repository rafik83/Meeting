<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Export;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExportFileAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;
    
    /** @var FileSystemAdapterInterface */
    private $fileSystemAdapter;
    
    /** @var string */
    private $exportProductsPath;
    
    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FileSystemAdapterInterface $fileSystemAdapter,
        string $exportProductsPath
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->fileSystemAdapter = $fileSystemAdapter;
        $this->exportProductsPath = $exportProductsPath;
    }
    
    /**
     * @param Event  $event
     * @param string $hash
     * @param File   $file
     *
     * @return CsvFileResponse
     */
    public function __invoke(Event $event, string $hash, File $file): CsvFileResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }
        
        if ($file->getHash() !== $hash) {
            throw new NotFoundHttpException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }
        
        $path = sprintf('%s%s', $this->exportProductsPath, $file->getPath());
        
        if (!$this->fileSystemAdapter->exists($path)) {
            throw new NotFoundHttpException(sprintf('File %s not found', $file->getId()));
        }
        
        return new CsvFileResponse(
            file_get_contents($path),
            sprintf('export_event_products_%s.csv', date('Y_m_d_His'))
        );
    }
}
