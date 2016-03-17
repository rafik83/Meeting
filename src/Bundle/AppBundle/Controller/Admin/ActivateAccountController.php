<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Admin\ActivateAccountPassword;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Admin\ActivateAccountPasswordType;
use Proximum\Vimeet\Domain\Model\Admin\ActivateAccountToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateAccountController extends Controller
{
    /**
     * @param Request              $request
     * @param ActivateAccountToken $activateAccountToken
     *
     * @return RedirectResponse|Response
     */
    public function passwordAction(Request $request, ActivateAccountToken $activateAccountToken)
    {
        $admin  = $activateAccountToken->getAdmin();

        if ($activateAccountToken->isExpired(new \DateTime())) {
            throw $this->createNotFoundException('The token is expired.');
        }

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->get('adapter.authentication_manager')->disconnect();
        }

        $command = new ActivateAccountPassword($admin);
        $form    = $this->createForm(ActivateAccountPasswordType::class, $command, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('command.admin.activate_account_password_handler')->handle($command);
            $this->get('adapter.authentication_manager')->authenticate($command->admin, 'admin');
            $this->addFlash('success', 'flash.admin.activate_account.success');

            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('VimeetAppBundle:Admin/ActivateAccount:password.html.twig', [
            'form'      => $form->createView()
        ]);
    }
}
