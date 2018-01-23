<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\UpdateEventDatesToCurrentDate;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EventDatesController extends Controller
{
    /**
     * @param Event   $event
     *
     * @return RedirectResponse
     */
    public function updateEventDatesAction(Event $event)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        try {
            $this->get('command.event.update_event_dates_to_current_date_handler')->handle(
                new UpdateEventDatesToCurrentDate($event)
            );
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_schedule_slots', [
            'event' => $event->getId(),
        ]);
    }
}
