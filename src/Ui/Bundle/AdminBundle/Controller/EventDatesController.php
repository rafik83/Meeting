<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\UpdateEventDatesToCurrentDate;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventDatesController extends Controller
{
    private const BEGIN_DATE =  'beginDate';

    public function updateEventDatesAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$this->getParameter('feature_event_dates_to_current_date_enabled')) {
            $this->createAccessDeniedException('Change event date is not available');
        }

        $form = $this->createFormBuilder([self::BEGIN_DATE => $this->get('datetime')])
            ->add(self::BEGIN_DATE, DateTimePickerType::class, [
                'display_hour' => false,
                'view_timezone' => $event->getTimeZone(),
                'format' => 'd/m/Y',
                'label' => $this->get('translator')->trans('admin.event.updateEventDates.begin'),
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->get('translator')->trans('common.validate'),
            ])
            ->getForm()
        ;

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('command.event.update_event_dates_to_current_date_handler')->handle(
                    new UpdateEventDatesToCurrentDate($event, $form->getData()[self::BEGIN_DATE])
                );

                return $this->redirectToRoute('admin_schedule_slots', [
                    'event' => $event->getId(),
                ]);
            } catch (\Exception $exception) {
                $this->addFlash('error', $exception->getMessage());
            }
        }

        return $this->render(
            '@Admin/Event/updateEventDates.html.twig',
            ['event' => $event, 'form' => $form->createView()]
        );
    }
}
