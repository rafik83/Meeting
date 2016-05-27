<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\User\ActivateAccountPassword;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\User\ActivateAccountPasswordType;
use Proximum\Vimeet\Domain\Model\User\ActivateAccountToken;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateAccountController extends Controller
{
    /**
     * @param Request              $request
     * @param EventView            $eventView
     * @param ActivateAccountToken $activateAccountToken
     *
     * @return RedirectResponse|Response
     */
    public function passwordAction(Request $request, EventView $eventView, ActivateAccountToken $activateAccountToken)
    {
        $sheet = $activateAccountToken->getSheet();
        $user  = $activateAccountToken->getUser();

        // We must refresh sheet to make behat feature working ...
        $this->getDoctrine()->getManager()->refresh($sheet);

        if ($activateAccountToken->isExpired(new \DateTime()) || !$sheet->hasUser($user)) {
            throw $this->createNotFoundException('The token is expired.');
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $command = new ActivateAccountPassword($user);
        $form    = $this->createForm(ActivateAccountPasswordType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->get('adapter.authentication_manager')->authenticate($command->user, 'main');

            return $this->redirectToRoute('event_account_participant_profile', [
                'sheet'       => $sheet->getId(),
                'participant' => $sheet->getUserParticipant($user)->getId()
            ]);
        }

        return $this->render('EventBundle:ActivateAccount:password.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView()
        ]);
    }
}
