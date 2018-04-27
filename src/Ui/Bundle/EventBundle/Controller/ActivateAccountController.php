<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\User\ActivateAccount\ReSendActivateAccountToken;
use Proximum\Vimeet\Application\Command\User\ActivateAccountPassword;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\ActivateAccountPasswordType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateAccountController extends Controller
{
    /**
     * @param Request              $request
     * @param EventDomain          $eventDomain
     * @param ActivateAccountToken $activateAccountToken
     *
     * @return RedirectResponse|Response
     */
    public function passwordAction(
        Request $request,
        EventDomain $eventDomain,
        ActivateAccountToken $activateAccountToken
    ) {
        $sheet = $activateAccountToken->getSheet();
        $user  = $activateAccountToken->getUser();

        // We must refresh sheet to make behat feature working ...
        $this->getDoctrine()->getManager()->refresh($sheet);

        if (!$sheet->hasUser($user) || $activateAccountToken->getSheet()->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($activateAccountToken->isExpired(new \DateTime())) {
            return $this->redirectToRoute('event_activate_account_expired', [
                'token' => $activateAccountToken->getToken(),
            ]);
        }

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $command = new ActivateAccountPassword($user, $sheet);
        $form    = $this->createForm(ActivateAccountPasswordType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->get('adapter.authentication_manager')->authenticate($command->user, 'main');

            return $this->redirectToRoute('event_account_participant', [
                'sheet'       => $sheet->getId(),
                'participant' => $sheet->getUserParticipant($user)->getId(),
            ]);
        }

        return $this->render('EventBundle:ActivateAccount:password.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request              $request
     * @param EventDomain          $eventDomain
     * @param ActivateAccountToken $activateAccountToken
     *
     * @return RedirectResponse|Response
     */
    public function expiredTokenAction(
        Request $request,
        EventDomain $eventDomain,
        ActivateAccountToken $activateAccountToken
    ) {
        $sheet = $activateAccountToken->getSheet();
        $user  = $activateAccountToken->getUser();

        // We must refresh sheet to make behat feature working ...
        $this->getDoctrine()->getManager()->refresh($sheet);

        if (!$sheet->hasUser($user) || $activateAccountToken->getSheet()->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $command = new ReSendActivateAccountToken($sheet, $user, $sheet->getOwner());

        if ($request->isMethod('POST')) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('reSendActivateAccountToken', 'confirm');

            return $this->redirectToRoute('event_actiavet_account_re_send_token_confirm');
        }

        return $this->render('EventBundle:ActivateAccount:expired.html.twig', [
            'event' => $eventDomain->getEvent(),
            'token' => $activateAccountToken->getToken(),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function confirmReSendTokenAction(Request $request, EventDomain $eventDomain)
    {
        if (empty($this->container->get('session')->getFlashBag()->get('reSendActivateAccountToken'))) {
            throw $this->createNotFoundException('Not allowed');
        }

        return $this->render('EventBundle:ActivateAccount:confirmReSendToken.html.twig', [
            'event' => $eventDomain->getEvent(),
        ]);
    }

    /**
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function completeProfileAction(Participant $participant)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $this->addFlash('login_email', $participant->getUser()->getEmail());

        return $this->redirectToRoute('event_account_participant', [
            'sheet'       => $participant->getSheet()->getId(),
            'participant' => $participant->getId(),
        ]);
    }
}
