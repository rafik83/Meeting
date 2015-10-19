<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\LoginType;
use Proximum\Vimeet\Domain\Model\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $event
     *
     * @return Response|RedirectResponse
     */
    public function loginAction(Request $request, EventView $event)
    {
        $subdomain = $request->attributes->get('subdomain');

        if (null !== $this->getUser()) {
            return $this->redirectToRoute('event', ['subdomain' => $subdomain]);
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $user = ['username' => $authenticationUtils->getLastUsername()];

        $form = $this->createForm(new LoginType(), $user, [
            'action' => $this->generateUrl('event_login_check', ['subdomain' => $subdomain]),
            'method' => 'POST',
        ]);

        return $this->render('VimeetAppBundle:Security:login.html.twig', [
            'event' => $event,
            'error' => $error,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $event
     *
     * @return Response|RedirectResponse
     */
    public function logoutConfirmationAction(Request $request, EventView $event)
    {
        $subdomain = $request->attributes->get('subdomain');

        if (null === $this->getUser()) {
            return $this->redirectToRoute('event', ['subdomain' => $subdomain]);
        }

        return $this->render('VimeetAppBundle:Security:logout_confirmation.html.twig', [
            'event' => $event,
        ]);
    }
}
