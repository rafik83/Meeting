<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Bundle\AppBundle\Form\Type\LoginType;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response|RedirectResponse
     */
    public function loginAction(Request $request, EventView $eventView)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $user = ['username' => $authenticationUtils->getLastUsername()];

        $form = $this->createForm(LoginType::class, $user, [
            'action' => $this->generateUrl('event_login_check'),
        ]);

        $users = $this->get('kernel')->getEnvironment() === 'dev' ?
            $this->get('vimeet_infrastructure.repository.user_repository')->all() :
            [];

        return $this->render('VimeetAppBundle:Event/Security:login.html.twig', [
            'eventView' => $eventView,
            'error'     => $error,
            'form'      => $form->createView(),
            'users'     => $users,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response|RedirectResponse
     */
    public function logoutConfirmationAction(Request $request, EventView $eventView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('VimeetAppBundle:Event/Security:logout_confirmation.html.twig', [
            'eventView' => $eventView,
        ]);
    }

    /**
     * @param User $user
     *
     * @return RedirectResponse
     */
    public function loginUserAction(User $user)
    {
        $this->get('adapter.authentication_manager')->authenticate($user);

        return $this->redirectToRoute('event');
    }
}
