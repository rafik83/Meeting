<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Nomenclature;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Nomenclature\Remove;
use Proximum\Vimeet\Application\Exception\Nomenclature\CanNotBeRemovedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RemoveController extends AbstractController
{
    private TranslatorInterface $translator;
    private CommandBusInterface $commandBus;

    public function __construct(
        TranslatorInterface $translator,
        CommandBusInterface $commandBus
    ) {
        $this->commandBus = $commandBus;
    }

    public function removeAction(Nomenclature $nomenclature): Response
    {
        $this->isGranted('IS_AUTHENTICATED_REMEMBERED');
        $event = $nomenclature->getEvent();

        if (null === $event && !$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException('The global nomenclature can only be delete by super admin');
        }

        if (null !== $event && !$this->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            throw $this->createAccessDeniedException('The nomenclature can only be delete by an organizer or a super admin');
        }

        if (null !== $event && $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw $this->createAccessDeniedException();
        }

        try {
            $this->commandBus->handle(new Remove($nomenclature));

            $this->addFlash('success', 'flash.admin.nomenclature.remove.success');
        } catch (CanNotBeRemovedException $exception) {
            $this->addFlash(
                'error',
                $this->translator->trans('validators.nomenclature.canNotBeRemoved', ['%title%' => $nomenclature->getTitle()], 'validators')
            );
        }

        if ($event instanceof Event) {
            return $this->redirectToRoute('admin_nomenclature_event', [
                'event' => $event->getId(),
            ]);
        }

        return $this->redirectToRoute('admin_nomenclature_globals');
    }
}
