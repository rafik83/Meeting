<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\Attend;
use Proximum\Vimeet\Application\Query\Sheet\Attend\SheetAttendanceViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\AttendanceChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     * @param Sheet   $sheet
     *
     * @return RedirectResponse|Response
     */
    public function cancelAttendanceAction(Request $request, Event $event, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('PERMISSION_SHEET_ACCESS', $sheet);

        if (!$this->isGranted('ROLE_ALLOWED_TO_ORGANIZE') || $event !== $sheet->getEvent()) {
            throw $this->createNotFoundException('Not allowed');
        }

        $meetingCount = $this->get('vimeet_infrastructure.repository.meeting_repository')->countMeetingsOfSheet($sheet);
        $view = $this->get('tactician.commandbus.query')->handle(new SheetAttendanceViewQuery($sheet));

        $attend = new Attend($sheet);
        $form   = $this->createForm(AttendanceChoiceType::class, $attend, [
            'submit'  => true,
            'confirm' => 'form.sheet_attendance_choice.children.submit.confirm',
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($attend);

            return $this->redirectToRoute('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ]);
        }

        return $this->render('AdminBundle:Sheet:attend.html.twig', [
            'event'        => $event,
            'form'         => $form->createView(),
            'meetingCount' => $meetingCount,
            'view'         => $view,
        ]);
    }

    /**
     * @param Event $event
     * @param Sheet $sheet
     *
     * @return RedirectResponse
     */
    public function redirectToCancelAttendanceAction(Event $event, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('PERMISSION_SHEET_ACCESS', $sheet);

        if ($event !== $sheet->getEvent()) {
            throw $this->createNotFoundException('Not allowed');
        }

        if (!$this->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            $this->addFlash('error', 'flash.admin.action.no-available');

            return $this->redirectToRoute('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ]);
        }

        return $this->redirectToRoute('admin_sheet_attend', [
            'event' => $event->getId(),
            'sheet' => $sheet->getId(),
        ]);
    }
}
