<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planning;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    /**
     * @param Event  $event
     * @param string $hash
     * @param File   $file
     *
     * @return Response
     */
    public function exportPlanningPrintAction(Event $event, $hash, File $file): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($file->getHash() !== $hash) {
            throw $this->createNotFoundException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        $path = sprintf('%s/%s', $this->getParameter('infrastructure.print_planning_path'), $file->getPath());

        if (!file_exists($path)) {
            throw $this->createNotFoundException(sprintf('File %s not found', $file->getId()));
        }

        return new Response(file_get_contents($path));
    }
}
