<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PrintController extends Controller
{
    /**
     * @param Event  $event
     * @param string $hash
     * @param File   $file
     *
     * @return BinaryFileResponse
     */
    public function generateAction(Event $event, string $hash, File $file)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($file->getHash() !== $hash) {
            throw $this->createNotFoundException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        if (!$this->get('filesystem')->exists($file->getPath())) {
            throw $this->createNotFoundException(sprintf('File %s not found', $file->getId()));
        }

        return new BinaryFileResponse($file->getPath());
    }
}
