<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product;

use Proximum\Vimeet\Application\Command\Product\Remove\Remove;
use Proximum\Vimeet\Application\Exception\Product\CanNotBeRemovedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RemoveController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     * @param Product $product
     *
     * @throws NotFoundHttpException
     *
     * @return RedirectResponse
     */
    public function removeAction(Request $request, Event $event, Product $product): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($product->getEvent() !== $event) {
            throw $this->createNotFoundException('This product is not on this event');
        }

        try {
            $this->get('tactician.commandbus')->handle(new Remove($product));

            $this->addFlash('success', 'flash.admin.product.remove.success');
        } catch (CanNotBeRemovedException $exception) {
            $this->addFlash('error',
                $this->get('translator')->trans(
                    'validators.product.canNotBeRemoved',
                    ['%productTitle%' => $product->getTitle($event->getAvailableLocale($request->getLocale()))],
                    'validators'
                )
            );
        }

        return $this->redirectToRoute('admin_product', [
            'event' => $event->getId(),
        ]);
    }
}
