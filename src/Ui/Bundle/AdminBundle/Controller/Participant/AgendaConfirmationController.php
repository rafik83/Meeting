<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Participant;

use Proximum\Vimeet\Application\Command\User\Event\Token\UpdateAgendaConfirmation;
use Proximum\Vimeet\Application\Query\Sheet\Detail\Participant\AgendaConfirmationStatusQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\User\Event\AgendaConfirmation\Constant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant\UpdateAgendaConfirmationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaConfirmationController extends Controller
{
    /**
     * @param Request     $request
     * @param Event       $event
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return RedirectResponse|Response
     */
    public function updateAgendaConfirmationAction(
        Request $request,
        Event $event,
        Sheet $sheet,
        Participant $participant
    ): Response {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('PERMISSION_SHEET_ACCESS', $sheet);

        if ($participant->getSheet() !== $sheet) {
            throw $this->createAccessDeniedException('The participant is not on this sheet');
        }

        $agendaConfirmationStatus = $this->get('tactician.commandbus.query')
            ->handle(new AgendaConfirmationStatusQuery($participant, $event))
        ;

        $status= Constant::getStatusFromView($agendaConfirmationStatus);
        $updateAgendaConfirmation = new UpdateAgendaConfirmation($event, $participant->getUser(), $status);
        $form = $this->createForm(UpdateAgendaConfirmationType::class, $updateAgendaConfirmation, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($updateAgendaConfirmation);

            return $this->redirectToRoute('admin_sheet_details', [
                'event' => $event->getId(),
                'sheet' => $sheet->getId(),
            ]);
        }

        return $this->render('AdminBundle:/User/Event/Token:agenda_confirmation_update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
