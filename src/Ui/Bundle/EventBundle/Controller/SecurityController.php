<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Model\Email;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Common\EmailType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Login\LoginType;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class SecurityController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response|RedirectResponse
     */
    public function loginFirstStepAction(Request $request, EventDomain $eventDomain)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        // Clean register type for potential next step
        $this->get('session')->getFlashBag()->get('register_type');

        $email = new Email();
        $form = $this->createForm(EmailType::class, $email);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            if ($this->get('vimeet_infrastructure.repository.user_repository')->emailExists($email->email)) {
                // clear potential previous email before setting new one
                $this->get('session')->getFlashBag()->get('login_email');
                $this->addFlash('login_email', $email->email);

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
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
            'users' => $users,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventDomain $eventDomain
     *
     * @return Response|RedirectResponse
     */
    public function loginSecondStepAction(Request $request, EventDomain $eventDomain)
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('event');
        }

        $typeFlashBag = $this->get('session')->getFlashBag()->get('register_type');
        $typeId       = array_shift($typeFlashBag);
        $type         = null;

        if (null !== $typeId) {
            if (is_int($typeId) && $type = $this->get('vimeet_infrastructure.repository.type_repository')->getTypeViewById($typeId, $request->getLocale())) {
                $this->addFlash('register_type', $typeId);
            } else {
                $typeId = null;
            }
        }

        $authenticationUtils = $this->get('security.authentication_utils');
        $error               = $authenticationUtils->getLastAuthenticationError();

        $email = $this->get('session')->getFlashBag()->get('login_email');

        if (empty($email) || null === ($email = array_shift($email))
            || !$this->get('vimeet_infrastructure.repository.user_repository')->emailExists($email)
        ) {
            return $this->redirectToRoute('event_login');
        }

        $this->addFlash('login_email', $email);

        $form = $this->createForm(LoginType::class, ['username' => $email], [
            'action' => $this->generateUrl('event_login_check'),
        ]);

        if (null !== $error) {
            $form->get('password')->addError(new FormError($error->getMessage()));
        }

        return $this->render('EventBundle:Security:login_second_step.html.twig', [
            'event'    => $eventDomain->getEvent(),
            'form'     => $form->createView(),
            'username' => $email,
            'error'    => $error,
            'typeId'   => $typeId,
            'type'     => $type,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response|RedirectResponse
     */
    public function logoutConfirmationAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        return $this->render('EventBundle:Security:logout_confirmation.html.twig', [
            'event'  => $eventDomain->getEvent(),
            'locale' => $request->getLocale(),
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
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function impersonatingUserAction(Request $request, Event $event)
    {
        $impersonatingUser = null;

        $token = $this->get('security.token_storage')->getToken();

        if (null !== $token) {
            $roles = $token->getRoles();

            foreach ($roles as $role) {
                if ($role instanceof SwitchUserRole) {
                    $impersonatingUser = $role->getSource()->getUser();
                }
            }
        }

        $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $request->getLocale());

        return $this->render('EventBundle:Security:impersonating.html.twig', [
            'impersonatingUser' => $impersonatingUser,
            'event'             => $event,
            'sheet'             => $sheet,
        ]);
    }
}
