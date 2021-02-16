<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\ExtraParameter;

use Proximum\Vimeet\Application\Command\Event\ExtraParameter\Remove;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RemoveController extends Controller
{
    /**
     * @param Event                $event
     * @param Event\ExtraParameter $extraParameter
     *
     * @return RedirectResponse
     */
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
        $this->get('tactician.commandbus')->handle($remove);

        return $this->redirectToRoute('admin_event_extra_parameter_list', [
            'event' => $event->getId(),
        ]);
    }
}
