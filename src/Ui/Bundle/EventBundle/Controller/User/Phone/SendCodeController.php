<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\User\Phone;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Phone\SendCodeForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile\PreUpdateHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class SendCodeController extends Controller
{
    /**
     * @param Request       $request
     * @param UserInterface $user
     * @param EventDomain   $eventDomain
     *
     * @return Response
     */
    public function sendCodeAction(Request $request, EventDomain $eventDomain, UserInterface $user): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $mobileNumber = $this->get('session.update_participant_session_manager')->getMobile();

        $sendCodeView = $this->get('handler.user.phone.send_code_form_handler')->handle(
            new SendCodeForm($request, $user, $eventDomain->getEvent(), $mobileNumber, true)
        );

        if ($sendCodeView->isSuccess()) {
            return $this->redirectToRoute('event_user_event_phone_validate_code');
        }

        return $this->render('EventBundle:SendCode:validateMobile.html.twig', [
            'event'        => $eventDomain->getEvent(),
            'sendCodeForm' => $sendCodeView->form !== null ? $sendCodeView->form->createView() : null,
            'tipTranslationViews' => $sendCodeView->tipTranslationViews
        ]);
    }

}
