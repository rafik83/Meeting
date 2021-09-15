<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Product\Remove\Remove;
use Proximum\Vimeet\Application\Exception\Product\CanNotBeRemovedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class RemoveController extends AbstractController
{
    private TranslatorInterface $translator;
    private CommandBusInterface $commandBus;

    public function __construct(
        TranslatorInterface $translator,
        CommandBusInterface $commandBus
    ) {
        $this->translator = $translator;
        $this->commandBus = $commandBus;
    }

    public function removeAction(Request $request, Event $event, Product $product): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if ($product->getEvent() !== $event) {
            throw $this->createNotFoundException('This product is not on this event');
        }

        try {
            $this->commandBus->handle(new Remove($product));

            $this->addFlash('success', 'flash.admin.product.remove.success');
        } catch (CanNotBeRemovedException $exception) {
            $this->addFlash('error',
                $this->translator->trans(
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
