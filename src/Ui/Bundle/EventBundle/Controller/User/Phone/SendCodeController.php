<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Security\Voter\ValidateMobileAccessVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class SendCodeController extends Controller
{
    /**
     * @param Request       $request
     * @param EventDomain   $eventDomain
     * @param UserInterface $user
     * @param Sheet         $sheet
     * @param Participant   $participant
     *
     * @return Response
     */
    public function sendCodeAction(Request $request, EventDomain $eventDomain, UserInterface $user, Sheet $sheet, Participant $participant): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(ValidateMobileAccessVoter::PERMISSION_NAME, $eventDomain->getEvent());

        $mobileNumber = $request->query->get('mobile', $user->getMobile());

        $sendCodeView = $this->get('handler.user.phone.send_code_form_handler')->handle(
            new SendCodeForm($request, $user, $eventDomain->getEvent(), $mobileNumber, true)
        );

        if ($sendCodeView->isSuccess()) {
            return $this->redirectToRoute('event_user_event_phone_validate_code', [
                'sheet'       => $sheet->getId(),
                'participant' => $participant->getId(),
            ]);
        }

        return $this->render('EventBundle:SendCode:validateMobile.html.twig', [
            'event'        => $eventDomain->getEvent(),
            'sendCodeForm' => $sendCodeView->form !== null ? $sendCodeView->form->createView() : null,
            'tipTranslationViews' => $sendCodeView->tipTranslationViews
        ]);
    }
}
