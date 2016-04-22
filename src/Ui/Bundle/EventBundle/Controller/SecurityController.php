<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Login\LoginFirstStepType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Login\LoginType;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class SecurityController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response|RedirectResponse
     */
    public function loginFirstStepAction(Request $request, EventView $eventView)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error               = $authenticationUtils->getLastAuthenticationError();
        $lastUsername        = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFirstStepType::class, ['email' => $lastUsername]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (null !== $data['email']
                && $this->get('vimeet_infrastructure.repository.user_repository')->emailExists($data['email'])
            ) {
                // clear potential previous username
                $this->get('session')->getFlashBag()->get('username');
                // set username
                $this->addFlash('username', $data['email']);

                return $this->redirectToRoute('event_login_second_step');
            }

            $error = new FormError($this->get('translator')->trans(
                'validators.login.email_not_exists',
                [],
                'validators',
                $request->getLocale()
            ));

            $form->get('email')->addError($error);
        }

        $users = $this->get('kernel')->getEnvironment() === 'dev' ?
            $this->get('vimeet_infrastructure.repository.user_repository')->all() :
            [];

        return $this->render('EventBundle:Security:login_first_step.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
            'error'     => $error,
            'users'     => $users,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response|RedirectResponse
     */
    public function loginSecondStepAction(Request $request, EventView $eventView)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event');
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error               = $authenticationUtils->getLastAuthenticationError();
        $username            = $authenticationUtils->getLastUsername();

        if (null === $username) {
            $username = $this->get('session')->getFlashBag()->get('username');

            if (empty($username) || null === ($username = array_shift($username))
                || !$this->get('vimeet_infrastructure.repository.user_repository')->emailExists($username)
            ) {
                return $this->redirectToRoute('event_login');
            }
        }

        $form = $this->createForm(LoginType::class, ['username' => $username], [
            'action' => $this->generateUrl('event_login_check'),
        ]);

        if (null !== $error) {
            $form->get('password')->addError(new FormError($error->getMessage()));
        }

        return $this->render('EventBundle:Security:login_second_step.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
            'username'  => $username,
            'error'     => $error,
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

        return $this->render('EventBundle:Security:logout_confirmation.html.twig', [
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
        $this->get('adapter.authentication_manager')->authenticate($user, 'main');

        return $this->redirectToRoute('event');
    }

    /**
     * @param EventView $eventView
     *
     * @return Response
     */
    public function impersonatingUserAction(EventView $eventView)
    {
        $impersonatingUser = null;

        if (null !== $token = $this->get('security.token_storage')->getToken()) {
            $roles = $token->getRoles();

            foreach ($roles as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonatingUser = $role->getSource()->getUser();
                }
            }
        }

        return $this->render('EventBundle:Security:impersonating.html.twig', [
            'impersonatingUser' => $impersonatingUser,
            'eventView'         => $eventView,
        ]);
    }
}
