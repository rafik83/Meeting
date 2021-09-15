<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteAll;
use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteMeeting;
use Proximum\Vimeet\Application\Exception\Meeting\NotAllowedToDeleteAllMeetingsException;
use Proximum\Vimeet\Application\Query\Meeting\Admin\Details\MeetingViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\View\Normalizer\EventMeetingsNormalizerView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Serializer\SerializerInterface;

class MeetingController extends AbstractController
{
    private SerializerInterface $serializer;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        SerializerInterface $serializer,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->serializer = $serializer;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function detailsAction(Request $request, Event $event, Meeting $meeting): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $meeting->getEvent()) {
            throw $this->createNotFoundException(
                sprintf('The meeting %s is not on the given event %s', $meeting->getId(), $event->getId())
            );
        }

        $meetingView = $this->queryBus->handle(
            new MeetingViewQuery($meeting, $event->getAvailableLocale($request->getLocale()))
        );

        return $this->render('AdminBundle:Meeting:details.html.twig', [
            'event'   => $event,
            'meeting' => $meetingView,
        ]);
    }

    public function deleteMeetingAction(Event $event, Meeting $meeting): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $meeting->getEvent()) {
            throw $this->createNotFoundException(
                sprintf('The meeting %s is not on the given event %s', $meeting->getId(), $event->getId())
            );
        }

        $this->commandBus->handle(new DeleteMeeting($meeting));

        return $this->redirectToRoute('admin_meeting_list', ['event' => $event->getId()]);
    }

    /**
     * This action delete all the meetings
     *
     * @param Event $event
     *
     * @return RedirectResponse
     */
    public function deleteAction(Event $event, AdminDomain $adminDomain)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if (!$this->getUser() instanceof Admin || !$this->getUser()->isSuperAdmin()) {
            throw $this->createNotFoundException('Action not allowed for this user');
        }

        try {
            $this->commandBus->handle(new DeleteAll($event, $adminDomain->getAdmin()));
        } catch (NotAllowedToDeleteAllMeetingsException $exception) {
            $this->addFlash('error', 'flash.admin.meeting.notAllowedToDeleteAllMeetingsException');
        }

        return $this->redirectToRoute('admin_meeting_list', ['event' => $event->getId()]);
    }

    public function exportAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $charset        = Charset::WINDOWS_1252;
        $normaliserView = new EventMeetingsNormalizerView($event);

        $exportContent = $this->serializer->serialize($normaliserView, 'csv', [
            'locale'        => $event->getAvailableLocale($request->getLocale()),
            'charset'       => $charset,
            'csv_delimiter' => ';',
        ]);

        $response    = new Response($exportContent);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'export_event_meetings_' . date('Y_m_d_His') . '.csv'
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', sprintf('text/csv; charset=%s', $charset));

        return $response;
    }
}
