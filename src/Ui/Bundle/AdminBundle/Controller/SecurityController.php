<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends Controller
{
    /**
     * @return RedirectResponse|Response
     */
    public function loginAction()
    {
        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('admin_event_list');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $admin = ['username' => $authenticationUtils->getLastUsername()];

        $form = $this->createForm(LoginType::class, $admin, [
            'action' => $this->generateUrl('admin_login_check'),
        ]);

        $admins = 'dev' === $this->get('kernel')->getEnvironment() ?
            $this->get('repository.admin_repository')->all() :
            [];

        return $this->render('AdminBundle:Security:login.html.twig', [
            'error'     => $error,
            'form'      => $form->createView(),
            'admins'    => $admins,
        ]);
    }

    /**
     * @param Admin $admin
     *
     * @return RedirectResponse
     */
    public function loginUserAction(Admin $admin)
    {
        $this->get('adapter.authentication_manager')->authenticate($admin, 'admin');

        return $this->redirectToRoute('admin_event_list');
    }
}
