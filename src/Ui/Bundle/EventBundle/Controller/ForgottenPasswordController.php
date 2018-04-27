<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Proximum\Vimeet\Application\Command\User\NewPassword;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\ForgottenPasswordType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\NewPasswordType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgottenPasswordController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse|Response
     */
    public function forgottenPasswordAction(Request $request, EventDomain $eventDomain)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $forgottenPassword = new ForgottenPassword($eventDomain->getEvent(), $request->getLocale());
        $form              = $this->createForm(ForgottenPasswordType::class, $forgottenPassword, [
            'action' => $this->generateUrl('event_forgotten_password'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($forgottenPassword);

                return $this->redirectToRoute('event_forgotten_password_confirm');
            } catch (EmailDoesNotExistException $exception) {
                $form->get('email')->addError($this->get('error_factory')->create('validators.emailDoesNotExist', $request->getLocale()));
            }
        }

        return $this->render('EventBundle:ResetPassword:request_token.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse|Response
     */
    public function forgottenPasswordConfirmAction(EventDomain $eventDomain)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        return $this->render('EventBundle:ResetPassword:confirm.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }

    /**
     * @param Request                $request
     * @param EventDomain            $eventDomain
     * @param ForgottenPasswordToken $forgottenPasswordToken
     *
     * @return RedirectResponse|Response
     */
    public function createNewPasswordAction(Request $request, EventDomain $eventDomain, ForgottenPasswordToken $forgottenPasswordToken)
    {
        if ($forgottenPasswordToken->isExpired(new \DateTime())) {
            throw $this->createNotFoundException('The token expired.');
        }

        $newPassword = new NewPassword($forgottenPasswordToken->getUser(), $eventDomain->getEvent());
        $form        = $this->createForm(NewPasswordType::class, $newPassword, [
            'action' => $this->generateUrl('event_create_new_password', [
                'token' => $forgottenPasswordToken->getToken(),
            ]),
            'method' => 'POST',
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($newPassword);
            $this->get('adapter.authentication_manager')->authenticate($forgottenPasswordToken->getUser(), 'main');
            $this->addFlash('success', 'flash.new_password.success');

            return $this->redirectToRoute('event');
        }

        return $this->render('EventBundle:ResetPassword:new_password.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
        ]);
    }
}
