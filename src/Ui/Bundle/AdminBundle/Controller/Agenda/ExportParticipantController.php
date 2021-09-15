<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ExportParticipantController extends AbstractController
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function indexAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        return $this->render('AdminBundle:Agenda:export.html.twig', [
            'event' => $event,
        ]);
    }
}
