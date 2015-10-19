<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

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
        if (null !== $this->getUser()) {
            return $this->redirectToRoute('event', ['subdomain' => $request->attributes->get('subdomain')]);
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'VimeetAppBundle:Security:login.html.twig',
            [
                'event'         => $event,
                'last_username' => $lastUsername,
                'error'         => $error,
            ]
        );
    }
}
