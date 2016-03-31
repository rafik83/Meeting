<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\User\ForgottenPassword;
use Proximum\Vimeet\Application\Command\User\NewPassword;
use Proximum\Vimeet\Application\Exception\User\EmailDoesNotExistException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\User\ForgottenPasswordType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\User\NewPasswordType;
use Proximum\Vimeet\Domain\Model\User\ForgottenPasswordToken;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgottenPasswordController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return RedirectResponse|Response
     */
    public function forgottenPasswordAction(Request $request, EventView $eventView)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event');
        }

        $forgottenPassword = new ForgottenPassword($eventView, $request->getLocale());
        $form              = $this->createForm(ForgottenPasswordType::class, $forgottenPassword, [
            'action' => $this->generateUrl('event_forgotten_password'),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('command.user.forgotten_password_handler')->handle($forgottenPassword);
                $this->addFlash('success', 'flash.reset_password_token.success');

                return $this->redirectToRoute('event');
            } catch (EmailDoesNotExistException $exception) {
                $form->get('email')->addError($this->get('error_factory')->create('validators.emailDoesNotExist', $request->getLocale()));
            }
        }

        return $this->render('EventBundle:ResetPassword:request_token.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request                $request
     * @param EventView              $eventView
     * @param ForgottenPasswordToken $forgottenPasswordToken
     *
     * @return RedirectResponse|Response
     */
    public function createNewPasswordAction(Request $request, EventView $eventView, ForgottenPasswordToken $forgottenPasswordToken)
    {
        if ($forgottenPasswordToken->isExpired(new \DateTime())) {
            throw $this->createNotFoundException('The token expired.');
        }

        $newPassword = new NewPassword($forgottenPasswordToken->getUser());
        $form        = $this->createForm(NewPasswordType::class, $newPassword, [
            'action' => $this->generateUrl('event_create_new_password', [
                'token' => $forgottenPasswordToken->getToken(),
            ]),
            'method' => 'POST',
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.user.new_password_handler')->handle($newPassword);
            $this->get('adapter.authentication_manager')->authenticate($forgottenPasswordToken->getUser(), 'main');
            $this->addFlash('success', 'flash.new_password.success');

            return $this->redirectToRoute('event');
        }

        return $this->render('EventBundle:ResetPassword:new_password.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }
}
