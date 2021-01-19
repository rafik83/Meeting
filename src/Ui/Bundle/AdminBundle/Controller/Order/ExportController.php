<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Order;

use Proximum\Vimeet\Application\Command\Order\Export\ExportJobCreator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ExportController extends Controller
{
    /**
     * @param Request       $request
     * @param UserInterface $admin
     * @param Event         $event
     *
     * @return RedirectResponse
     */
    public function exportAction(Request $request, UserInterface $admin, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$admin instanceof Admin) {
            throw $this->createNotFoundException('Admin not found');
        }

        $exportJobCreator = new ExportJobCreator($event, $admin, $request->getLocale());

        $this->get('tactician.commandbus')->handle($exportJobCreator);

        $this->addFlash('success', 'flash.admin.order.export.success');

        return $this->redirectToRoute('admin_sheet_order_list', ['event' => $event->getId()]);
    }

    /**
     * @param Event  $event
     * @param string $hash
     * @param File   $file
     *
     * @return CsvFileResponse
     */
    public function exportFileAction(Event $event, $hash, File $file)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($file->getHash() !== $hash) {
            throw $this->createNotFoundException(
                sprintf('File %s has a different hash from the one given %s', $file->getId(), $hash)
            );
        }

        $path = sprintf('%s%s', $this->getParameter('infrastructure.export_order_path'), $file->getPath());

        if (!$this->get('filesystem')->exists($path)) {
            throw $this->createNotFoundException(sprintf('File %s not found', $file->getId()));
        }

        return new CsvFileResponse(
            file_get_contents($path),
            sprintf('export_event_orders_%s.csv', date('Y_m_d_His'))
        );
    }
}
