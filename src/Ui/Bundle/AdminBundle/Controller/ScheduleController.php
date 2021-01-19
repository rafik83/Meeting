<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Schedule\Configure;
use Proximum\Vimeet\Application\Query\Schedule\SlotViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule\ConfigureType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ScheduleController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function slotsAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new Configure($event);
        $form    = $this->createForm(ConfigureType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.schedule.configure.success');

            return $this->redirectToRoute('admin_schedule_slots', ['event' => $event->getId()]);
        }

        $slots = $this->get('tactician.commandbus')->handle(new SlotViewQuery($event));

        return $this->render('AdminBundle:Schedule:slots.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
            'slots' => $slots,
            'featureEventDatesToCurrentDateEnabled' => $this
                ->container
                ->getParameter('feature_event_dates_to_current_date_enabled'),
        ]);
    }
}
