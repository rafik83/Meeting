<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Agenda;

use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Eluceo\iCal\Property\Event\Attendees;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Application\View\Agenda\DayView;
use Proximum\Vimeet\Application\View\Agenda\MeetingView;
use Symfony\Component\HttpFoundation\Response;

class IcalHandler
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function handle(AgendaView $agendaView): Response
    {
        $vCalendar = new Calendar('vimeet.events');
        $vCalendar->setName($agendaView->sheet->getEvent()->getTitle());

        /** @var DayView $day */
        foreach ($agendaView->days as $day) {
            foreach ($day->happenings as $happening) {
                $vEvent = new Event();
                $vEvent->setDtStart($happening->getBegin());
                $vEvent->setDtEnd($happening->getEnd());
                $vEvent->setSummary($happening->title);
                $vEvent->setDescription($happening->description);
                $vEvent->setUseTimezone(true);
                $vEvent->setIsPrivate(true);

                if ($happening->webinar) {
                    $vEvent->setUrl(
                        $this->router->generateAbsoluteUrl(
                            'event_sheet_happening_webinar',
                            ['sheet' => $agendaView->sheet->getId(), 'happening' => $happening->id]
                        )
                    );
                }

                $vCalendar->addComponent($vEvent, $happening->id);
            }

            /** @var MeetingView $meeting */
            foreach ($day->meetings as $meeting) {
                $vEvent = new Event();

                $summary = implode(', ', array_map(fn ($t) => $t->getTitle(), $meeting->sheetMetTitle));

                $description = '';
                $attendees = new Attendees();

                foreach ($meeting->participants as $participantView) {
                    $description .= empty($description) ? '' : ' ; ';
                    $participantText = sprintf(
                        '%s %s - %s',
                        $participantView->card->firstname,
                        $participantView->card->lastname,
                        $summary
                    );
                    $description .= $participantText . ' (' . $participantView->card->position . ')' . PHP_EOL;
                    $attendees->add($participantText);
                }

                foreach ($meeting->meetingOwnSheetParticipantViews as $participantView) {
                    $participantText = sprintf(
                        '%s %s - %s',
                        $participantView->firstName,
                        $participantView->lastName,
                        $meeting->userSheetTitle
                    );
                    $description .= $participantText;
                    $attendees->add($participantText);
                }

                $vEvent->setDtStart($meeting->getBegin());
                $vEvent->setDtEnd($meeting->getEnd());
                $vEvent->setSummary($summary);
                $vEvent->setDescription($description);

                $vEvent->setAttendees($attendees);
                $vEvent->setLocation($meeting->spotRef);
                $vEvent->setUrl(
                    $this->router->generateAbsoluteUrl(
                        'event_agenda_participant',
                        ['sheet' => $agendaView->sheet->getId(), 'participant' => $agendaView->participant->getId()]
                    )
                );
                $vEvent->setIsPrivate(true);
                $vEvent->setUseTimezone(true);

                $vCalendar->addComponent($vEvent, $meeting->id);
            }
        }

        $response = new Response($vCalendar->render());
        $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s.ics"', $agendaView->sheet->getEvent()->getTitle())
        );

        return $response;
    }
}
