<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Participant;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ExportAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var string */
    private $uploadDir;

    /** @var FileSystemAdapterInterface */
    private $fileSystem;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FileSystemAdapterInterface $fileSystem,
        string $uploadDir
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->uploadDir = $uploadDir;
        $this->fileSystem = $fileSystem;
    }

    /**
     * CSV export of participant's filtered sheets. Requires super admin or organizer role.
     *
     * @param Event  $event
     * @param File   $file
     * @param string $hash
     *
     * @return BinaryFileResponse
     */
    public function __invoke(Event $event, File $file, $hash): BinaryFileResponse
    {
        if (false === $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || false ===  $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $file->getHash() !== $hash
            || !$this->fileSystem->exists(sprintf('%s%s', $this->uploadDir, $file->getPath()))
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $response = new BinaryFileResponse(
            sprintf('%s%s', $this->uploadDir, $file->getPath())
        );

        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', Charset::WINDOWS_1252));

        return $response;
    }
}
