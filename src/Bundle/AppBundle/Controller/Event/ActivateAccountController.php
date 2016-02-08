<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use DateTime;
use Elastica\Exception\NotFoundException;
use Proximum\Vimeet\Application\Command\User\ActivateAccountPassword;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\User\ActivateAccountPasswordType;
use Proximum\Vimeet\Domain\Model\ActivateAccountToken;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateAccountController extends BaseController
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
        if (new DateTime() > $activateAccountToken->getExpireDate()
        || null === $this->get('vimeet_infrastructure.repository.participant_repository')->getParticipantForUserAndSheet($activateAccountToken->getUser(), $activateAccountToken->getSheet())
        ) {
            throw new NotFoundException('Date of the token expired');
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
        }

        $sheet = $activateAccountToken->getSheet();
        $user  = $activateAccountToken->getUser();
        $activateAccountPassword = new ActivateAccountPassword($user);

        $form = $this->createForm(ActivateAccountPasswordType::class, $activateAccountPassword, [
            'action' => $this->generateUrl('event_activate_account', [
                'token' => $activateAccountToken->getToken(),
            ]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.user.activate_account_password_handler')->handle($activateAccountPassword);
            $this->authenticate($activateAccountPassword->user);

            $this->addFlash('success', 'flash.activate_account.success');

            return $this->redirectToRoute('event_sheet_update_participant', [
                'sheet'       => $sheet->getId(),
                'participant' => $this->get('vimeet_infrastructure.repository.participant_repository')->getParticipantForUserAndSheet($user, $sheet)->getId()
            ]);
        }

        return $this->render('VimeetAppBundle:Event/ActivateAccount:password.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView()
        ]);
    }
}
