<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Components\Type\HasAvailabilityManagementEnabled;
use Proximum\Vimeet\Application\Components\Type\HasUnavailabilityManagementDisabled;
use Proximum\Vimeet\Application\Query\Agenda\AgendaViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\ParticipantView;
use Proximum\Vimeet\Application\View\Agenda\AgendaView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\AgendaAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AgendaController extends Controller
{
    public function indexAction(
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain
    ): RedirectResponse {
        $this->checkAccess($eventDomain, $sheet);

        $user = $userDomain->getUser();
        $participant = $sheet->getUserParticipant($user);

        $isUserAloneParticipant = null !== $user
            ? ParticipantHelper::isUserAloneParticipant($user, $sheet)
            : false;

        $isUserParticipantMultipleSheet = $this->get('vimeet_infrastructure.repository.sheet_repository')
            ->isUserParticipantMultipleSheetsInEvent($user, $eventDomain->getEvent());

        if (!$isUserAloneParticipant || $isUserParticipantMultipleSheet) {
            return $this->redirectToRoute(
                'event_agenda_sheet',
                [
                    'sheet' => $sheet->getId(),
                ]
            );
        }

        return $this->redirectToRoute(
            'event_agenda_participant',
            [
                'participant' => null !== $participant ? $participant->getId() : $sheet->getFirstParticipant()->getId(),
                'sheet' => $sheet->getId(),
            ]
        );
    }

    public function participantAction(
        EventDomain $eventDomain,
        Request $request,
        Participant $participant,
        Sheet $sheet,
        UserDomain $userDomain
    ): Response {
        $this->checkAccess($eventDomain, $sheet);

        if ($participant->getSheet() !== $sheet) {
            throw $this->createNotFoundException('This participant is not in this sheet');
        }

        if ($this->get(IsParticipantVisio::class)->isSatisfiedBy($participant) && !$participant->getTimezone()) {
            return $this->redirectToRoute('event_participant_timezone', [
                'participant' => $participant->getId(),
                'sheet' => $sheet->getId(),
            ]);
        }

        $user = $userDomain->getUser();

        /** @var AgendaView $agenda */
        $agenda = $this->get('tactician.commandbus.query')->handle(new AgendaViewQuery(
            $eventDomain->getEvent(),
            $sheet,
            $participant,
            $request->getLocale(),
            $user
        ));

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
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

        $myParticipant = $sheet->getUserParticipant($user);
        $otherParticipants = $agenda->participants;

        if ($myParticipant !== null) {
            $otherParticipants = \array_filter($agenda->participants, function (ParticipantView $otherParticipant) use ($myParticipant) {
                return $myParticipant->getId() !== $otherParticipant->id;
            });
        }




        return $this->render(
            '@Event/Agenda/participant_agenda.html.twig',
            [
                'event' => $eventDomain->getEvent(),
                'agenda' => $agenda,
                'sheet' => $sheet,
                'myParticipant' => $myParticipant,
                'otherParticipants' => $otherParticipants,
                'tipTranslationViews' => $tipTranslationViews,
                'sendCodeForm' => $sendCodeForm,
                'sendCodeViewTranslationViews' => $sendCodeViewTranslationViews,
                'ignorePhoneConfirmationUrl' => $ignorePhoneConfirmationUrl,
                'participant' => $participant,
                'isUnavailabilityManagementDisabled' => $this->get(HasUnavailabilityManagementDisabled::class)->isSatisfiedBy($sheet),
                'isAvailabilityManagementEnabled' => $this->get(HasAvailabilityManagementEnabled::class)->isSatisfiedBy($sheet),
            ]
        );
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
