<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\SheetsAvailableBySlotQuery;
use Proximum\Vimeet\Application\Query\Agenda\MeetingPropositionFromAvailableSheets\MeetingPropositionFromAvailableSheetsQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class AgendaController extends Controller
{
    /**
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return RedirectResponse
     */
    public function indexAction(EventDomain $eventDomain, Sheet $sheet, UserInterface $user)
    {
        $this->checkAccess($eventDomain, $sheet);

        $participant = $sheet->getUserParticipant($user);

        if (null !== $participant) {
            return $this->redirectToRoute(
                'event_agenda_participant',
                ['participant' => $participant->getId(), 'sheet' => $sheet->getId()]
            );
        }

        return $this->redirectToRoute(
            'event_agenda_participant',
            ['participant' => $sheet->getFirstParticipant()->getId(), 'sheet' => $sheet->getId()]
        );
    }

    /**
     * @param EventDomain   $eventDomain
     * @param Request       $request
     * @param Participant   $participant
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return Response
     */
    public function participantAction(
        EventDomain $eventDomain,
        Request $request,
        Participant $participant,
        Sheet $sheet,
        UserInterface $user
    ): Response {
        $this->checkAccess($eventDomain, $sheet);

        if ($participant->getSheet() !== $sheet) {
            throw $this->createNotFoundException('This participant is not in this sheet');
        }

        /** @var AgendaView $agenda */
        $agenda = $this->get('tactician.commandbus.query')->handle(new AgendaViewQuery(
            $eventDomain->getEvent(),
            $sheet,
            $participant,
            $request->getLocale(),
            $user
        ));

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $participant->getSheet()->getType(),
            TipTranslationViewQueryHandler::CONTEXT_AGENDA,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        $sendCodeForm = null;
        $ignorePhoneConfirmationUrl = null;
        $sendCodeViewTranslationViews = null;

        if ($agenda->isPhoneValidationRequired && $participant->getUser() === $user) {
            $mobileNumber = $request->query->get('mobile', $user->getMobile());
            $actionRoute = $this->generateUrl(
                'event_user_phone_validate',
                [
                    'sheet' => $sheet->getId(),
                    'participant' => $participant->getId(),
                    'redirectTo' => $this->generateUrl(
                        'event_agenda_participant',
                        [
                            'sheet' => $sheet->getId(),
                            'participant' => $participant->getId(),
                        ]
                    ),
                ]
            );

            $sendCodeView = $this->get('handler.user.phone.send_code_form_handler')->handle(
                new SendCodeForm(
                    $request,
                    $user,
                    $eventDomain->getEvent(),
                    $actionRoute,
                    $mobileNumber
                )
            );

            $sendCodeForm = null !== $sendCodeView->form ? $sendCodeView->form->createView() : null;
            $sendCodeViewTranslationViews = $sendCodeView->tipTranslationViews;
            $ignorePhoneConfirmationUrl = $this->generateUrl('event_agenda_ignore_phone_confirmation', [
                'sheet'       => $sheet->getId(),
                'participant' => $participant->getId(),
            ]);
        }

        return $this->render('EventBundle:Agenda:index.html.twig', [
            'event'                        => $eventDomain->getEvent(),
            'agenda'                       => $agenda,
            'sheet'                        => $sheet,
            'tipTranslationViews'          => $tipTranslationViews,
            'sendCodeForm'                 => $sendCodeForm,
            'sendCodeViewTranslationViews' => $sendCodeViewTranslationViews,
            'ignorePhoneConfirmationUrl'   => $ignorePhoneConfirmationUrl,
        ]);
    }

    /**
     * @param EventDomain   $eventDomain
     * @param UserInterface $user
     * @param Sheet         $sheet
     * @param MeetingSlot   $slot
     *
     * @return JsonResponse
     */
    public function countSheetsAvailableBySlotAction(
        EventDomain $eventDomain,
        UserInterface $user,
        Sheet $sheet,
        MeetingSlot $slot
    ): JsonResponse {
        $this->checkAccess($eventDomain, $sheet);

        if (!$sheet->isInCatalog()) {
            throw $this->createAccessDeniedException('Sheet not in catalog');
        }

        $participant = $sheet->getUserParticipant($user);

        if (null === $participant) {
            return new JsonResponse(['message' => 'participant not found'], 404);
        }

        $countAvailableSheetsWithProposition = $this
            ->get('tactician.commandbus.query')
            ->handle(new MeetingPropositionFromAvailableSheetsQuery($sheet, $slot))
        ;

        if ($countAvailableSheetsWithProposition > 0) {
            return new JsonResponse(
                [
                    'message' => $this->renderView(
                        'EventBundle:Agenda/AvailableSlot:availableRequestForSlot.html.twig',
                        [
                            'countAvailableSheetsWithProposition' => $countAvailableSheetsWithProposition,
                            'sheet'                               => $sheet,
                            'slot'                                => $slot,
                        ]
                    ),
                    'countAvailableSheets' => $countAvailableSheetsWithProposition,
                ]
            );
        }

        $countAvailableSheets = $this
            ->get('tactician.commandbus.query')
            ->handle(new SheetsAvailableBySlotQuery($eventDomain->getEvent(), $sheet, $slot))
        ;

        $message = 0 === $countAvailableSheets
            ? ''
            : $this->renderView(
                'EventBundle:Agenda/AvailableSlot:availableSheetForSlot.html.twig',
                [
                    'countAvailableSheets' => $countAvailableSheets,
                    'event'                => $eventDomain->getEvent(),
                    'sheet'                => $sheet,
                    'slot'                 => $slot,
                ]
            );

        return new JsonResponse(['message' => $message, 'countAvailableSheets' => $countAvailableSheets]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     */
    private function checkAccess(EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);
        $this->denyAccessUnlessGranted(AgendaAccessVoter::PERMISSION, $eventDomain->getEvent());
    }
}
