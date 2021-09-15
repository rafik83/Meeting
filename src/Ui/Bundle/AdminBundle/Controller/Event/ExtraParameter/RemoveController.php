<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\ExtraParameter;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Remove;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RemoveController extends AbstractController
{
    private CommandBusInterface $commandBus;

    public function __construct(
        CommandBusInterface $commandBus
    ) {
        $this->commandBus = $commandBus;
    }

    public function removeAction(Event $event, Event\ExtraParameter $extraParameter): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        if ($event !== $extraParameter->getEvent()) {
            throw $this->createNotFoundException(
                sprintf('the extra parameter %s is not on the event %s', $extraParameter->getId(), $event->getId())
            );
        }

        $remove = new Remove($extraParameter);
        $this->commandBus->handle($remove);

        return $this->redirectToRoute('admin_event_extra_parameter_list', [
            'event' => $event->getId(),
        ]);
    }
}
