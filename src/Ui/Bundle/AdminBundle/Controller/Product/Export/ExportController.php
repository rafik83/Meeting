<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Export;

use Proximum\Vimeet\Application\Command\Catalog\Export\ExportProductsJobCreator;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
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
    public function exportAction(Request $request, UserInterface $admin, Event $event): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');
        
        if (!$admin instanceof Admin) {
            throw $this->createNotFoundException('Admin not found');
        }
        
        $exportJobCreator = new ExportProductsJobCreator($event, $admin, $request->getLocale());
        
        $this->get('tactician.commandbus')->handle($exportJobCreator);
        
        $this->addFlash('success', 'flash.admin.export.products.success');
        
        return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
    }
}
