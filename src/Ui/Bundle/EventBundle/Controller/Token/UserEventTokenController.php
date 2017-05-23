<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Token;

use Proximum\Vimeet\Domain\Model\Token\UserEventToken;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEventTokenController extends Controller
{
    /**
     * @param EventDomain    $eventDomain
     * @param UserEventToken $userEventToken
     * @param UserInterface  $user
     *
     * @return Response
     */
    public function confirmAgendaAction(EventDomain $eventDomain, UserEventToken $userEventToken, UserInterface $user = null)
    {
        if ($userEventToken->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException('Token invalid');
        }

        if ($user instanceof User && $user !== $userEventToken->getUser()) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        if ($userEventToken->isConfirmed()) {
            return $this->render('EventBundle:Token\UserEventToken:agenda_confirmation.html.twig', [
                'event'             => $eventDomain->getEvent(),
                'already_confirmed' => true,
            ]);
        }

        return $this->render('EventBundle:Token\UserEventToken:agenda_confirmation.html.twig', [
            'event'             => $eventDomain->getEvent(),
            'already_confirmed' => false,
        ]);
    }
}
